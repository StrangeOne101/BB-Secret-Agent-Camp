<?php
$open = true;
global $open;

include_once("../debug.php");
include_once("../emails.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!(isset($_POST["to"]) && isset($_POST["body"]) && isset($_POST["pin"]))) {
        $error = "Not all parameters were submitted!";
    } else {
        if ($_POST["pin"] != "1883") {
            $error = "Incorrect pin!";
        } else {
            if (emailSMTP($_POST["to"], "Test Message", $_POST["body"])) {
                $message = "Email sent!";
            } else {
                $error = "Failed for unknown reason!";
            }
        }
    }

}

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

    <title>C&R Camp | Debug Email</title>

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
    <form method="post" action="sendEmail.php">
        <?php if (isset($message)) echo "<div class=\"alert alert-info\" role=\"alert\">" . $message . "</div>"; ?>
        <?php if (isset($error)) echo "<div class=\"alert alert-danger\" role=\"alert\">" . $error . "</div>"; ?>

        <div class="form-group">
            <label for="toAddreess" class="left-label">To</label>
            <input name="to" type="email" class="form-control" id="toAddreess" placeholder="Enter Email">
        </div>
        <div class="form-group">
            <label for="emailbody" class="left-label">Email</label>
            <textarea name="body" class="form-control" id="emailbody" placeholder="Email Body"></textarea>
        </div>
        <div class="form-group">
            <label class="left-label">Security PIN</label>
            <input name="pin" type="number" class="form-control password" maxlength="4" placeholder="XXXX">
        </div>
        <button type="submit" id="submitme" class="btn btn-primary btn-block">Submit</button>
        <small id="warning" class="form-text text-muted">Make sure the site is secure before submitting.</small>
    </form>
</div>

</body>
</html>
