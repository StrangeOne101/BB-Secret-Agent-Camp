<?php
$open = true;
global $open;

include("../scripts/debug.php");
include("../scripts/database.php");

if (!isReady()) { //If the database is broken. If so... fek
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!(isset($_SESSION["email"])) || getPermission($_SESSION["email"]) < 64) {
        echo "Error: Insufficient privileges!";
        return;
    }

    if (!isset($_POST['email']) || !isset($_POST['firstname']) || !isset($_POST['lastname']) || !isset($_POST['permission'])) {
        echo "Error: Requires the email, firstname, lastname and permission to be sent!";
        return;
    }
    $username = $_POST['email']; //The submitted username
    $permission = $_POST['permission'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];

    //TODO Put this in the tbl_login_pending table and send email with a link with the token in it
} else {
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        header("HTTP/1.1 403 Forbidden");
        return;
    }
}

?>
