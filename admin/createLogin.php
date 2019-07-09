<?php
$open = true;
global $open;

include($_SERVER['DOCUMENT_ROOT'] . "/scripts/debug.php");
include($_SERVER['DOCUMENT_ROOT'] . "/scripts/database.php");
include($_SERVER['DOCUMENT_ROOT'] . "/scripts/tokens.php");
include($_SERVER['DOCUMENT_ROOT'] . "/scripts/emails.php");

if (!isReady()) { //If the database is broken. If so... fek
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    session_start();
    if (isset($_SESSION["token"])) { //If they are verifying an existing token
        $token = $_SESSION["token"];
        if (!isset($_POST["password"])) {
            echo "Error: No password provided!";
            return;
        }

        $query = "SELECT * FROM $TABLE_LOGINS_PENDING WHERE `Token` = \"$token\"";
        $result = $database->query($query);
        if (!$result && $result->num_rows > 0) { //If there is a return
            echo "Error: Token not found in the database!";
            return;
        }
        $row = $result->fetch_assoc();

        if (!createLogin($row["Email"], $_POST["password"], $row["FirstName"], $row["LastName"], intval($row["Permission"]))) {
            echo "Error: Failed to create login!";
            return;
        }
        $query = "DELETE FROM $TABLE_LOGINS_PENDING WHERE `Token` = \"$token\"";
        $database->query($query);

        $_SESSION["email"] = $row["Email"];
        $_SESSION["permission"] = intval($row["Permission"]);
        $_SESSION["firstname"] = $row["FirstName"];
        unset($_SESSION["token"]); //Destroy the token that we keep over sessions as it conflicts with this page

        header("Location: ./index.php");
    } else { //If they are creating one to append to the db. This should send out the email as well.
        if (!(isset($_SESSION["email"])) || getPermission($_SESSION["email"]) < 64) {
            echo "Error: Insufficient privileges!";
            return;
        }

        if (!isset($_POST['email']) || !isset($_POST['firstname']) || !isset($_POST['lastname']) || !isset($_POST['permission'])) {
            echo "Error: Requires the email, firstname, lastname and permission to be sent!";
            return;
        }
        $email = $_POST['email']; //The submitted username
        $permission = $_POST['permission'];
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $token = createToken(16);
        $date = date( 'Y-m-d H:i:s', strtotime("+24 hours"));


        global $TABLE_LOGINS_PENDING, $database;

        $query = "INSERT INTO $TABLE_LOGINS_PENDING (FirstName, LastName, Permission, Token, Email, Expiry) VALUES (\"$firstname\", \"$lastname\", $permission, \"$token\", \"$email\", \"$date\")";

        if (!$database->query($query)) {
            echo "Error: Failed to insert into the database! " . $database->error;
        }

        $headers = "From: Space Camp <info@spacecamp.co.nz>\r\nMime-Version: 1.0\r\nContent-Type: text/html; charset=ISO-8859-1\r\nReturn-Path: <info@spacecamp.co.nz>";
        $subject = "Space Camp Admin Registration";

        $vars = array("firstname" => $firstname, "lastname" => $lastname, "token" => "https://" . $_SERVER["HTTP_HOST"] . "/admin/invite.php?token=" . $token, "email" => $email);
        $message = getEmailFile("admin_creation.txt", $vars); //Gets the email from the admin_creation.txt file

        /*if (!mail("$firstname $lastname <$email>", "$subject", "$message", "$headers")) {
            echo "Error: Failed to send mail - " . getLastError()["message"];
        }*/

        emailSMTP($email, $subject, $message);
    }

} else {
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        header("HTTP/1.1 403 Forbidden");
        return;
    }
}

?>
