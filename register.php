<!DOCTYPE html>
<html lang="en">

<?php 
$open = true;
global $open;

include 'Database.php';

$name = $email = $address = $postcode = $food = $medical = $phone = $phonemobile = $agentID = "";
$ecname = $ecphone = "";
$registeetype = 0;
$company = -1;
$officer = 0;

$part = $part2 = "";

$dob = "";

$DEBUG = false;
$captchaValid = true;
global $DEBUG;

global $captchaValid, $validating, $name, $address, $postcode, $company, $dob, $email, $food, $medical, $phone, $phonemobile, $agentID, $ecname, $ecphone, $officer;

$validating = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	foreach ($_POST as $key => $value)
	    if ($DEBUG) {
	        echo "Field ".htmlspecialchars($key)." is ".htmlspecialchars($value)."<br>";
	    }
		
	
	$name = $_POST["name"];
	$email = $_POST["email"];
	$address = $_POST["address"];
	$dob = $_POST["dateofbirth"];
	if (isset($_POST["company"])) {
		$company = $_POST["company"];
		if ($company >= 0) {
		    $companystring = $GLOBALS["companies"][$company];
		}
	} 
	
	$postcode = $_POST["postcode"];
	$food = $_POST["food"];
	$medical = $_POST["medical"];
	$phone = $_POST["phone"];
	$phonemobile = $_POST["phonemobile"];
	$agentID = $_POST["agentid"];
	$ecname = $_POST["ecname"];
	$ecphone = $_POST["ecphone"];
	if (!empty($_POST["officer"])) {
	    $officer = 1;
	}
	
	$response = $_POST["g-recaptcha-response"];
	
	
	if (empty($name) || empty($email) || empty($address) || empty($dob) || $company == 0 || empty($postcode) 
			|| (empty($phone) || empty($phonemobile)) || empty($agentID) || empty($ecname) || empty($ecname)) {
				$validating = true;
	} else {
		//The form is valid. Register the user.
	    if (!checkCaptcha($response)) {
	        $captchaValid = false;
	    } else {
	        register($name, $dob, $agentID, $address, $postcode, $phone, $phonemobile, $email, ($company -1), $food, $medical, $ecname, $ecphone, $officer);
	    }
	}
	
	if ($DEBUG) {
	    echo "Company : " . ($company - 1);
	    echo "Address: " . str_replace("\n", ", ", str_replace(" \n", ", ", $address));
	}
	
} 

if (empty($agentID)) {
	$part1 = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 2);
	$part2 = substr(str_shuffle("00112233445566778899"), 0, 3);
	
	$agentID = $part1 . $part2;
}
//0 = firstname
//1 = lastname

function validate($input, $type = 0) {
	if (!$GLOBALS['validating']) {
		echo "class=\"form\"";
		return;
	}

	$output = "class=\"form ";
	if ($type == 1 && !filter_var($input, FILTER_VALIDATE_EMAIL)) {
		$output = $output . "invalid-email";//Append 
	} else if ($type == 2 && !preg_match("/\w.*\s.*\w/", $input)) {
		$output = $output . "invalid-name";
	} else if ($type == 3 && $input <= 0) { 
		$output = $output . "invalid-company";
	}else if ($input == null || $input == "") {
		$output = $output . "invalid";
	} 
	
	$output = $output . "\"";
	
	
	echo $output;
}

function checkCaptcha($key) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, array(
        'secret' => "6LeZUCkUAAAAAOCMu3OcO6zpJCie60EC8hzr2IXK",
        'response' => $key,
        'remoteip' => $_SERVER["REMOTE_ADDR"]
    ));
    $curlData = curl_exec($curl);
    curl_close($curl);
    
    $recaptcha = json_decode($curlData, true);
    
    if ($recaptcha["success"]) {
        return true;
    }
    
    return false;
}
?>

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Toby Strange (StrangeOne101)">
	<meta name="robots" content="noindex">
    <meta name="googlebot" content="noindex">
    
    <title>Secret Agent Camp - Register</title>

    <!-- Bootstrap Core CSS -->
    <!-- <link href="css/bootstrap.min.css" rel="stylesheet"> -->

    <!-- Custom CSS -->
   	<link href="css/register.css" rel="stylesheet">
    
    <link rel="shortcut icon" href="favicon.ico">
    
    <script src="js/jquery-3.2.1.slim.min.js"></script>
    <script src="js/register.js"></script>
    
    <!-- Google's Captcha API - Prevents bots from flooding our DB -->
    <script src='https://www.google.com/recaptcha/api.js'></script> 
    <!--<script src="js/bootstrap.min.js"></script> -->
	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

	<script>
	
	var debug = true;
	
	$(document).ready(function() {
		if (debug) {
			$(".debug-hidden").removeClass("debug-hidden"); //Makes it non hidden
		}
	});
	</script>
</head>

<body>
		<h1>Register for Secret Agent Camp</h3>
        <div id="register-form">
        	<!--  <div class="offset-xl-3 col-xl-6 offset-lg-3 col-lg-6 offset-md-1 col-md-10">-->
        	
        	<!-- <img id="register_img" src="../img/register.png">-->
        	<form id="register_form_form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        		<div class="form-group">
	        	<input id="form-firstname" <?php validate($name, 2)?> name="name" placeholder="John Smith" type="text" maxlength="40" value="<?php echo $name?>">
	        	<!--  <?php validate(1)?>
	        	<input id="form-lastname" class="form" name="lname" placeholder="Smith" type="text" value="">
				-->
				<input id="form-agentID" class="form" disabled name="agentid" type="text" maxlength="5" value="<?php echo $agentID?>">
	        	
	        	<select id="form-company" <?php validate($company, 3)?> name="company"  value="<?php echo $company?>">
		        	<option value="0" disabled selected>** Please Select **</option>
		        	<?php         							
		        		for($i = 0; $i < count($GLOBALS["companies"]); $i++) {
		        			$ii = $i + 1;
		        			echo "<option value='$ii' " . ($company == $ii ? "selected" : "") . ">" . $GLOBALS["companies"][$i] . "</option>";
		        		}
		        		?>
	        	</select>
	        	
	        	<input id="form-officer" class="form" type="checkbox" value="1" name="officer" <?php if ($officer == 1) echo "checked" ?>>
	        	
	        	<input id="form-dob" <?php validate($dob)?> name="dateofbirth" value="<?php echo $dob?>" type="date">
				
	        	<input id="form-email" <?php validate($email, 1)?> name="email" placeholder="example@domain.com" type="text" maxlength="50" value="<?php echo $email?>">
	        	<input id="form-phone" <?php validate($phone)?> name="phone" type="text" value="<?php echo $phone?>" maxlength="15">
	        	<input id="form-phonemobile" <?php validate($phonemobile)?> name="phonemobile" type="text" value="<?php echo $phonemobile?>" maxlength="15">
	        	
	        	<textarea cols="3" id="form-address" <?php validate($address)?>  name="address" placeholder="99 Example Drive" maxlength="60" type="text"><?php echo $address?></textarea>
	        	
	        	<input id="form-postcode" <?php validate($postcode)?> name="postcode" placeholder="0000" max="9999" maxlength="4" type="number" value="<?php echo $postcode?>">
	        	
	        	<textarea id="form-medical" <?php validate("-")?>  name="medical" placeholder="" maxlength="1024" type="text"><?php echo $medical?></textarea>
	        	<textarea id="form-food" <?php validate("-")?>  name="food" placeholder="" maxlength="512" type="text"><?php echo $food?></textarea>
	        	
	        	<input id="form-ecname" <?php validate($ecname)?> placeholder="Mr. Smith" name="ecname" maxlength="20" type="text" value="<?php echo $ecname?>">
	        	<input id="form-ecphone" <?php validate($ecphone)?> name="ecphone" type="text" maxlength="15" value="<?php echo $ecphone?>">
	        	
	        	<?php if ($validating) { //The form isn't valid still, or we will have been redirected
	        		echo "<span id='form-invalid'>Please fill out all the fields marked in red, then try again.</span>";
	        	}
	        	
	        	if (!$captchaValid) { //The captcha failed to verify them
	        	    echo "<span id='form-invalid'>The captcha failed to verify you. Please try submitting again.</span>";
	        	}
	        	?>
	        	
	        	<!-- The button that is for the re-captcha -->
	        	<button id="re-captcha--" class="g-recaptcha" data-sitekey="6LeZUCkUAAAAALIvQt51RFJhAZt2upmJo2tNApqz" data-callback="submitForm"> Submit </button>
	        	
	        	
        	</form>
        		<!--</div>-->
       
    </div>
     <!-- Page Content -->
    
</body>
</html>