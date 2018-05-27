<?php
$open = true;
global $open;

include("../scripts/debug.php");
include("../scripts/database.php");
include("../scripts/tokens.php");
include("../scripts/emails.php");

if (!isReady()) { //If the database is broken. If so... fek
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    session_start();
    if (isset($_SESSION["token"])) { //If they are verifying an existing token

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

        $database->query($query);

        $headers = "From: Space Camp<info@spacecamp.co.nz>; Content-Type: text/html; charset=ISO-8859-1\r\n";
        $subject = "Space Camp Admin Registration";

        $variables = array("firstname" => $firstname, "lastname" => $lastname, "token" => $token, "email" => $email);
        $message = getEmailFile("admin_creation.txt", $variables); //Gets the email from the admin_creation.txt file

        mail($email, $subject, $message, $headers);

        //TODO Put this in the tbl_login_pending table and send email with a link with the token in it
    }

} else {
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        header("HTTP/1.1 403 Forbidden");
        return;
    }
}

?>
