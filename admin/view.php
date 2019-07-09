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

include_once($_SERVER['DOCUMENT_ROOT'] . "/scripts/debug.php");

global $debugVal;
$debugVal = false; //So debug doesn't echo

include_once($_SERVER['DOCUMENT_ROOT'] . "/scripts/database.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/scripts/commonqueries.php");
//Start of the real stuff and not just imports

$lost = false;

function showErrorPage() {
    $myfile = fopen($_SERVER['DOCUMENT_ROOT'] . "/admin/pages/error_database_admin.html", "r"); //Open the file
    if ($myfile == null) {
        echo "Something went really wrong!"; //o shit son
        exit();
    }
    echo str_replace('$errors', getErrors(), fread($myfile,filesize($_SERVER['DOCUMENT_ROOT'] . "/admin/pages/error_database_admin.html"))); //Echo the data, and fill in the errors
    fclose($myfile); //Because we are a tidy kiwi
}

/*function postToDBQuery($query, $params) {
	$url = 'dbquery.php';
	$data = array('queryno' => $query, 'parameters' => $params);

// use key 'http' even if you send the request to https://...
	$options = array(
		'http' => array(
			'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
			'method'  => 'POST',
			'content' => http_build_query($data)
		)
	);
	$context  = stream_context_create($options);
	$result = file_get_contents($url, false, $context);
	if ($result === FALSE) { /* Handle error */ //}

	/*var_dump($result);
}*/

if (!isReady()) {
    showErrorPage();
    return;
}

function createDatabaseTable($query) {
    $data = runQuery($query);

    echo "<table class=\"table table-striped database-table\">";
    echo "<thead>";
    echo "<tr>";
    echo "<th scope=\"col\">#</th>"; //The # is the name of the field for row number

    //Echo each field name in the table
    while ($field = mysqli_fetch_field($data)) {
        echo "<th scope=\"col\">" . $field->name . "</th>";
    }

    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";

    $row_count = 0;
    $last_row = null;
    while($row = mysqli_fetch_assoc($data)) { //For every single row in the SQL table
        $row_count++;

        echo "<tr>"; //Start row
        echo "<td scope=\"row\">" . $row_count ."</td>"; //Echo the row number

        foreach ($row as $value) { //Go through each field and echo the value
            echo "<td>" . $value . "</td>";
        }

        echo "</tr>"; //End row
        $last_row = $row;
    }

    echo "</tbody>";
    echo "</table>";
}

if (!isset($_GET["token"])) {
    $lost = true;
} else { //Load token and user details
    global $database, $TABLE_LOGINS, $TABLE_TOKENS;

    $token = $_GET["token"];

    if (strlen($token) > 32) {
        $token = substr($token, 0, 32);
        header("Location: view.php?token=" . $token);
    }

    $query = "SELECT * FROM $TABLE_TOKENS WHERE `Token` = '$token'";
    $result = $database->query($query);


    if (!$result) { //If this fails, which it shouldn't. However if it does, show this error page.
        debug("Error from the database (how???): " . $database->error);
        showErrorPage();
        return;
    }
    if ($result->num_rows <= 0) { //If there is nothing in the returned query
        $lost = true;
    } else {
		$row = $result->fetch_assoc(); //Fetch first bit of data, which is the only bit of data

		$queryNo = $row["QueryID"];
		$title = $row["Title"];
		$parameters = $row["Parameters"];

		if ($parameters == "") {
		    $parameters = 0;
        }

		session_start(); //Start sessions

		if (!isset($_SESSION["token"])) { //Load the token into their session so that queries to dbquery.php are allowed
			$_SESSION["token"] = $token;
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

    <title>Cops and Robbers Camp Registrations Viewer</title>

    <!-- Bootstrap Core CSS -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="css/basic.css" rel="stylesheet">
    <link href="css/login.css" rel="stylesheet">
    <link href="css/dashboard.css" rel="stylesheet">
    <link href="css/view.css" rel="stylesheet">

    <link rel="shortcut icon" href="../favicon.ico">
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

    <?php
        if (!$lost) {
            echo "<script>var database = {}; database.queryno = $queryNo; database.parameters = $parameters;</script>";
        }
    ?>
    <script src="../js/databasetable.js"></script>
    <script src="js/view.js"></script>
</head>
<body>
<nav class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0">
    <a class="navbar-brand col-sm-3 col-md-2 mr-0" href="#">Space Camp Registrations</a>
    <!--<div class="form-control form-control-dark w-100"></div>-->
    <ul class="navbar-nav px-3">
        <li class="nav-item text-nowrap">

        </li>
        <!--<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#tabbedSideBar" aria-controls="tabbedSideBar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>-->
    </ul>
</nav>


<div class="" style="margin: 30px 30px 30px 30px;">
    <div class="header">
        <h3 class="pb-2" style="display: inline-block"><?php echo $title; ?></h3>
        <input type="button" id="downloadCSV" class="btn btn-lg btn-primary" <?php  if ($lost) echo "disabled" ?> value="Download as CSV">
    </div>
    <div id="databaseTable" class="dbtable"><?php
        if ($lost) {
            echo "<h4>Cannot load due to invalid token!</h4>";
        } else {
			if ($queryNo == "0") $query = getRegistrationQuery();
			else if ($queryNo == "1") {
				if ($parameters == "" || $parameters == null) {
					echo "<h4>Error: No company parameter given!</h4>";
					return;
				} else if (!intval($parameters)) {
					echo "<h4>Error: Company parameter must be an int!</h4>";
					return;
				}
				$query = getRegistrationsByCompanyQuery(intval($parameters));
			} else if ($queryNo == "2") {
				$query = getRecentRegistrations();
			} else if ($queryNo == "3") {
				$query = getDietaryRegistrations();
			} else if ($queryNo == "4") {
				$query = getMedicalRegistrations();
			} else {
				echo "<h4>Error: Unknown common query with ID $queryNo!</h4>";
				return;
			}

			createDatabaseTable($query);
        }

    ?>
    </div>
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
                        <h5 style="font-weight: normal">You seem to have ended up in the wrong place by accident! If you are sure you are in the right place, please contact <a href="mailto:strange.toby@gmail.com">Toby</a> to sort this issue.</h5>
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
}
?>

</body>
</html>
