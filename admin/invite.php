<?php
/**
 * Created by PhpStorm.
 * Author: StrangeOne101 (Toby Strange)
 */

if ($_SERVER["REQUEST_METHOD"] != "GET") {
    header("HTTP/1.1 403 Forbidden");
    exit(); //Prevent HTTP requests
}

if (isset($_GET["token"])) {
    $token = $_GET["token"];
} else {
    header("HTTP/1.1 403 Forbidden"); //Prevent people from seeing this page
    exit();
}

$open = true;
global $open;

include_once("../scripts/debug.php");
include_once("../scripts/database.php");

if (!isReady()) { //If the database is broken. If so... fek
    header("Location: index.php"); //Shows error message
    exit();
}

$verified = false;
$name = $email = $password  = "";
$permission = 0;
$expiry;

function checkToken($token) {
    global $database, $TABLE_LOGINS_PENDING, $verified, $name, $email, $permission, $password, $expiry;

    $token = $database->real_escape_string($token);

    $query = "SELECT * FROM $TABLE_LOGINS_PENDING WHERE `Token` = \"$token\"";
    $result = $database->query($query);
    if ($result && $result->num_rows > 0) { //If there is a return
        $row = $result->fetch_assoc();
        $name = $row["FirstName"];
        $email = $row["Email"];
        $permission = $row["Permission"];
        $expiry = $row["Expiry"];

        if (strtotime($expiry) >= time()) { //If the expiry hasn't expired yet
            $verified = true;
            session_start();
            $_SESSION["token"] = $token; //This allows the server to see that the POST request we send when setting
                                         //the password is from the same person that accessed the token
        }
    }
}

checkToken($token);
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Toby Strange (StrangeOne101)">
    <meta name="robots" content="noindex">
    <meta name="googlebot" content="noindex">

    <meta http-equiv="cache-control" content="no-cache, must-revalidate, post-check=0, pre-check=0" />
    <meta http-equiv="cache-control" content="max-age=0" />
    <meta http-equiv="expires" content="0" />
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <meta http-equiv="pragma" content="no-cache" />

    <title>C&R Camp | Admin Signup</title>

    <!-- Bootstrap Core CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="css/basic.css" rel="stylesheet">
    <link href="css/login.css" rel="stylesheet">

    <link rel="shortcut icon" href="favicon.ico">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="js/invite.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body>
<div id="panel" class="container" align="center">
    <form method="post" action="<?php echo $verified ? "createLogin.php" : ""; //Only echo the url if the token is verified ?>">
        <?php
        if ($verified) {
            echo '<div class="alert alert-info" role="alert">Welcome, ' . $name . '! Please create a password.</div>';
        } else {
            echo '<div class="alert alert-danger" role="alert">Either the link is invalid or has expired. Please request another one.</div>';
        }
        ?>
        <div class="form-group">
            <label for="passwordField1" class="left-label">Password</label>
            <input name="password" type="password" <?php echo $verified ? "" : "disabled";?> class="form-control password-field" id="passwordField1" placeholder="Enter Password">
        </div>
        <div class="form-group">
            <label for="passwordField2" class="left-label">Verify Password</label>
            <input name="" type="password" <?php echo $verified ? "" : "disabled";?> class="form-control password-field" id="passwordField2" aria-describedby="warning" placeholder="Verify Password">
            <small id="makesureyoutypethemright" class="form-text text-muted text-red" style="height: 16px; font-size: 14px;"> </small>
        </div>
        <button type="submit" id="submitme" class="btn btn-primary btn-block" disabled>Submit</button>
        <small id="warning" class="form-text text-muted">Make sure the site is secure before submitting.</small>
    </form>
</div>

</body>
</html>




