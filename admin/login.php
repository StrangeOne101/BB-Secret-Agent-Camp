<?php
if((!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != "on") && $_SERVER["HTTP_HOST"] != "localhost")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}

$open = true;
global $open;

include("../scripts/debug.php");
include("../scripts/database.php");

$message = "";

if (!isReady()) { //If the database is broken. If so... fek
    header("Location: index.php");
    exit();
}

session_start(); //Start sessions. Allow us to persist data across pages. We do it now to test if a user logged out.

if ($_SERVER["REQUEST_METHOD"] == "POST") { //If they have just tried to login
    $email = $_POST['email']; //The submitted username
    $password = $_POST['password']; //And password
    
    if (isValidLogin($email, $password)) { //Run the query
        //Put in basic information. Not the password, though. Obviously.

        $_SESSION['username'] = $email; //Deprecated
        $_SESSION['email'] = $email;
        $names = getName($email);
        $_SESSION['firstname'] = $names[0];
        $_SESSION['lastname'] = $names[1];
        
        debug("Successfully logged in");
        header("Location: https://" . $_SERVER["HTTP_HOST"] . "/admin/");
        exit();
    } else {
        $message = "Invalid login details.";
    }
} else if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $email = isset($_GET['email']) ? $_GET['email'] : ""; //The submitted username
    $password = isset($_GET['password']) ? $_GET['password'] : "";
}

if ($message == "") {
    if (isset($_SESSION["loggedout"])) {
        $message = "You have been logged out.";
        unset($_SESSION["loggedout"]);
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
        
        <title>Adminstrator Login</title>
    
    	<!-- Bootstrap Core CSS -->
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    
        <!-- Custom CSS -->
       	<link href="../css/admin/basic.css" rel="stylesheet">
       	<link href="../css/admin/login.css" rel="stylesheet">
       	
        <link rel="shortcut icon" href="../favicon.ico">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script src="../js/debug.js"></script>
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
			<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
				<?php 
				if ($message != "") {
				    echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($message) .'</div>';
				}
				?>
              	<div class="form-group">
                	<label for="exampleInputEmail1" class="left-label">Email address</label>
                	<input name="email" type="email" class="form-control" id="exampleInputEmail1" placeholder="Enter email" value="<?php
                	if ($_SERVER["REQUEST_METHOD"] == "GET" || ($_SERVER["REQUEST_METHOD"] == "POST" && $message != "")) {echo $email;}
                	?>">
              	</div>
              	<div class="form-group">
                	<label for="exampleInputPassword1" class="left-label">Password</label>
                	<input name="password" type="password" class="form-control" id="exampleInputPassword1" aria-describedby="warning" placeholder="Password" value="<?php if ($_SERVER["REQUEST_METHOD"] == "GET") echo $password; ?>">
                	<small id="warning" class="form-text text-muted">Make sure the site is secure before logging in.</small>
              	</div>
              	<button type="submit" class="btn btn-primary btn-block">Login</button>
              	<small id="forgotpass" class="form-text text-muted">Forgot your password? Sorry bud. No <a href="#">link</a> yet.</small>
            </form>
		</div>
		
	</body>
</html>