<?php
if((!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != "on") && $_SERVER["HTTP_HOST"] != "localhost")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}
$open = true;
global $open;

include("scripts/debug.php");

$loggedIn = false;
$user = "";
$priv = 0;

session_start();

if(isset($_SESSION['username'])){
    $loggedIn = true;
    $user = $_SESSION['username'];
    //$priv = $_SESSION['permissions'];
} else {
    
}

$startHour = 18; //6PM
$start = "2018-08-17 +" . ($startHour - 10) . " hours";
$timeOffset = $_SERVER["HTTP_HOST"] != "localhost" ? "+12 hours" : ""; //Offset is +12 hours, but is not offset when hosted locally

//header("Location: register.php");

$your_date = strtotime($start . " " . $timeOffset);
$datediff = $your_date - time();

$days = floor($datediff / (60 * 60 * 24));
global $days;

?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Toby Strange (StrangeOne101)">
	<!--<meta name="robots" content="noindex">  <!-- This has been commented out as a trial to let google index the page -->
    <!--<meta name="googlebot" content="noindex"> -->
    
    <meta http-equiv="cache-control" content="no-cache, must-revalidate, post-check=0, pre-check=0" />
  	<meta http-equiv="cache-control" content="max-age=0" />
  	<meta http-equiv="expires" content="0" />
  	<meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
  	<meta http-equiv="pragma" content="no-cache" />
    
    <title>Space Camp - Home</title>

	<!-- Bootstrap Core CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
   	<link href="css/index.css" rel="stylesheet">
   	
    <link rel="shortcut icon" href="favicon.ico">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="js/debug.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    
</head>

<body>

	<div id="tile">
		<div class="center" id="title_center">
			<!--<h3>Space Camp... Lift Off In...</h3>
			<h1 id="sac-timer"><?php echo $days?> Days</h1>
			
			<input id="register-btn" type="button" class="btn btn-large btn-lg" title="Register" value="Register Here!" onclick="document.location='/register.php'">
			<h5>Registrations close on August 11th</h5>-->

            <div id="banner">
                <img src="img/header_t.png" width="80%">
                <h3 style="margin-top: -20px"><?php
                    if ($days >= 1) {
                        echo "SPACE CAMP! LIFT OFF IN ";
                        echo $days . " DAY" . ($days == 1 ? "" : "S");
                    } else {
                        global $datediff;

                        $hours = floor($datediff / (60 * 60));
                        $mins = floor($datediff / (60));

                        if ($hours >= 1) {
                            echo "SPACE CAMP! LIFT OFF IN ";
                            echo ($hours + 1) . " HOUR" . ($hours == 1 ? "" : "S");
                        } else if ($mins >= 1) {
							echo "SPACE CAMP! LIFT OFF IN ";
							echo $mins . " MINUTE" . ($mins == 1 ? "" : "S");
                        } else if ($days >= -2) {
                            echo "SPACE CAMP ONGOING! WOOHOO";
                        } else {
                            echo "SPACE CAMP OVER! THANKS FOR COMING";
                        }
                    }
                    echo "!";
                    ?></h3>
            </div>
            <input id="register-btn" type="button" disabled class="btn btn-large btn-lg" title="Register" value="Registrations closed!" style="border: 8px solid #505050; color: #505050">
			<!-- <h4>Registrations <?php
                if ($days > 7) {
                    echo "close in " . ($days - 7) . " day" . (($days - 7) == 1 ? "" : "s") . "!";
                } else if ($days == 7) {
					echo "close today! Hurry!";
                } else {
                    echo "closed!";
                }
                ?></h4> -->
            <h4 style="font-size: 48px;">The shuttle's left already, contact us at <a href="mailto:info@spacecamp.co.nz">info@spacecamp.co.nz</a> and we might be able to beam you up.</h4>
		</div>
		<div class="admin-buttons"><form action="/admin"><input type="submit" class="btn btn-large btn-danger invisible" value="Admin Panel"/></form></div>
	</div>
    <div class="icons">
        <a href="https://www.bbcanterbury.org.nz/"><img class="img-icon" src="img/logo-bb.png"></a>
        <a href="http://site.iconz.org.nz/"><img class="img-icon" src="img/logo-iconz.png"></a>
    </div>
	<?php 
	/*if ($loggedIn) {
	    echo "<span class='administrate'>Admin Panel</span>";
	} else {
	    debug("No session!");
	}*/
	?>
</body>
</html>