<!DOCTYPE html>
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
    
    <title>Secret Agent Camp - Thanks</title>

	<!-- Bootstrap Core CSS -->
    <!--  <link href="css/bootstrap.min.css" rel="stylesheet">-->

    <!-- Custom CSS -->
   	<link href="css/thanks.css" rel="stylesheet">
   	
    <link rel="shortcut icon" href="favicon.ico">
    <script src="js/jquery-3.2.1.slim.min.js"></script>
    <!--<script src="js/bootstrap.min.js"></script> -->
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
			<h1>Thanks for registering!</h1>
			<?php session_start();
			
			if (isset($_SESSION["email"])) {
			    //echo "You should recieve an email shortly.";
			    echo "<h4>We've sent an email to <u>" . $_SESSION["email"] . "</u>. You should recieve it shortly.</h4>";
			} 
			else {
			    echo "<h3>You should recieve an email about it shortly.</h3>";
			}
			?>
			<h3>Want to register another child? Click <a href="/register.php" >here</a>.</h3>
		</div>
	</div>
</body>
</html>