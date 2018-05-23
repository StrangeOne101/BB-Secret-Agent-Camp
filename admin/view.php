<?php
/**
 * Created by PhpStorm.
 * Author: StrangeOne101 (Toby Strange)
 * Date: 14-May-18
 */

if ($_SERVER["REQUEST_METHOD"] != "GET") { //GET only
    header("HTTP/1.1 403 Forbidden");
    return;
}

$open = true; //So the database doesn't complain that we are using it incorrectly and give us a 403
global $open;

include("../scripts/debug.php");

global $debugVal;
$debugVal = false; //So debug doesn't echo

include("../scripts/database.php");

//Start of the real stuff and not just imports

$lost = false;

function showErrorPage() {
    $myfile = fopen("./pages/error_database.html", "r"); //Open the file
    if ($myfile == null) {
        echo "Something went really wrong!"; //o shit son
        return "";
    }
    echo str_replace('$errors', getErrors(), fread($myfile,filesize("./pages/error_database.html"))); //Echo the data, and fill in the errors
    fclose($myfile); //Because we are a tidy kiwi
}

if (!isReady()) {
    showErrorPage();
    return;
}

if (!isset($_GET["token"]) || !isValidToken($_GET["token"])) {
    $lost = true;
} else { //Load token and user details
    global $database, $TABLE_LOGINS, $TABLE_TOKENS;

    $query = "SELECT * FROM $TABLE_LOGINS WHERE `Password` = '$token'";
    $result = $database->query($query);


    if (!$result) { //If this fails, which it shouldn't. However if it does, show this error page.
        debug("Error from the database (how???): " . $database->error);
        showErrorPage();
        return;
    }

    $query = "SELECT * FROM $TABLE_TOKENS WHERE `UserID` = " . $result->fetch_assoc()["UserID"] ;
    $result2 = $database->query($query);

    if (!$result2) { //If this fails, which it shouldn't. However if it does, show this error page.
        debug("Error from the database while fetching token queries: " . $database->error);
        showErrorPage();
        return;
    }

    session_start(); //Session time!

    //Don't load a new session if an existing user exists. If we did this, an existing user viewing the page will be logged out
    if (!isset($_SESSION["email"]) || $_SESSION["email"] == $result->fetch_assoc()["Email"]) {
        $_SESSION["email"] = $result->fetch_assoc()["Email"];
        $_SESSION["firstname"] = $result->fetch_assoc()["FirstName"];
        $_SESSION["lastname"] = $result->fetch_assoc()["LastName"];
        //$_SESSION["Permission"] = $result->fetch_assoc()["Permission"]; //No longer needed
        $_SESSION["readquery"] = $result2->fetch_assoc()["ReadQuery"];
        $_SESSION["writequery"] = $result2->fetch_assoc()["WriteQuery"];
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

    <title>Space Camp Data Editor</title>

    <!-- Bootstrap Core CSS -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="../css/admin/basic.css" rel="stylesheet">
    <link href="../css/admin/login.css" rel="stylesheet">
    <link href="../css/admin/dashboard.css" rel="stylesheet">

    <link rel="shortcut icon" href="favicon.ico">
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/bootstrap.bundle.js"></script>
    <script src="../js/debug.js"></script>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <script>var query = "<?php
        if (!$lost) {
            echo $_SESSION["readquery"];
        }
    ?>";</script>
    <script src="../js/view.js"></script>
    <style>
        body {
            background-color: #F0F0F0;
        }

        .dbtable {
            overflow: scroll;
            max-height: 300px;
            min-height: 40px;
        }

        #downloadCSV {
            float: right;
            margin: 30px 0px 30px 0px;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0">
    <a class="navbar-brand col-sm-3 col-md-2 mr-0" href="#">Space Camp Data Editor</a>
    <!--<div class="form-control form-control-dark w-100"></div>-->
    <ul class="navbar-nav px-3">
        <li class="nav-item text-nowrap">
            <a class="nav-link" id="welcomeDropdown" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Welcome, <?php
                if (isset($_SESSION["firstname"])) {
                    echo $_SESSION["firstname"];
                } else {
                    echo "User";
                }

            ?>!</a>
        </li>
        <!--<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#tabbedSideBar" aria-controls="tabbedSideBar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>-->
    </ul>
</nav>
<div class="" style="margin: 30px 30px 30px 30px;">
    <div id="databaseTable" class="dbtable"><h3>Loading content...</h3></div>
    <input type="button" id="downloadCSV" class="btn btn-lg btn-primary" value="Download as CSV">
</div>

<?php
if ($lost) {
    echo '<div class="modal" tabindex="-1" role="dialog" id="lostModal">
        <div class="modal-dialog modal-lg" role="document" style="">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" style="">Uh Oh!</h2>
                </div>
                <div class="modal-body container">
                    <div class="">
                        <p style="float: right; margin-left: 20px; margin-right: 20px;"><img src="../img/lost.png" width="200px"></p>
                        <h5 style="font-weight: normal">You seem to have ended up in the wrong place by accident! If you are sure you are in the right place, please contact Toby to sort this issue.</h5>
                    </div>
    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script>$(document).ready(function() {
        $("#lostModal").modal("show");
    });</script>';
} else {

}
?>

</body>
</html>
