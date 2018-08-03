<?php

if($_SERVER["HTTPS"] != "on" && $_SERVER["HTTPS"] != "on" && $_SERVER["HTTP_HOST"] != "localhost")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}

$open = true;
global $open;

include("scripts/debug.php");

global $debugVal;
$debugVal = false;

include('scripts/database.php');
include('scripts/commonqueries.php');
include('scripts/emails.php');

$name = $email = $address = $postcode = $food = $medical = $phone = $phonemobile = $agentID = "";
$ecname = $ecphone = "";
$registeetype = 0;
$company = -1;
$personType = 1; //1 = boy, 2 = parent help, 3 = officer, 4 = other
$photoPerm = 1;

$part = $part2 = "";

$dob = "";

$DEBUG = false; //WARNING, Header redirects do not work when DEBUG is on!
$captchaValid = true;
global $DEBUG;

global $captchaValid, $validating, $name, $address, $postcode, $company, $dob, $email, $food, $medical, $phone, $phonemobile, $agentID, $ecname, $ecphone, $personType, $photoPerm;

$validating = false;


if (!isReady()) {
	$myfile = fopen("./admin/pages/error_database.html", "r"); //Open the file
	if ($myfile == null) {
		echo "Something went really wrong!"; //o shit son
		return "";
	}
	echo str_replace('$errors', getErrors(), fread($myfile,filesize("./admin/pages/error_database.html"))); //Echo the data, and fill in the errors
	fclose($myfile); //Because we are a tidy kiwi
    exit();
}

$result = runQuery(getCompanies());
$companies = array();
$companiesPayToCompany = array();

for ($i = 0; $i < $result->num_rows; $i++) {
	$currentRow = $result->fetch_assoc();
	$companies[$currentRow["CompanyID"]] = $currentRow["CompanyName"];
	$companiesPayToCompany[$currentRow["CompanyID"]] = $currentRow["PayingAsCompany"];

	if ($DEBUG) {
	    echo $currentRow["CompanyID"] . " => " . $currentRow["CompanyName"] . "\n";
    }
}


if ($DEBUG) {
    echo "<br>" . isset($companies["1"]) . " | " . isset($companies[1]);
}


/**
 * Generates a reference number used for billing
 * @return string
 */
function genRefNo() {
	$refno = "#BB";

	for ($int = 0; $int < 8; $int++) {
		$refno = $refno . rand(0, 9);
	}
	return $refno;
}

/**
 * Registers a new person in the database
 * @param $name
 * @param $dob
 * @param $agentID
 * @param $address
 * @param $postcode
 * @param $phone
 * @param $phonemobile
 * @param $email
 * @param $company
 * @param $food
 * @param $medical
 * @param $ecname
 * @param $ecphone
 * @param $type
 * @param $photoPerm
 */
function register($name, $dob, $agentID, $address, $postcode, $phone, $phonemobile, $email, $company, $food, $medical, $ecname, $ecphone, $type, $photoPerm) {
	try {

	    global $TABLE_REGISTRATIONS, $database, $companies, $DEBUG;

		$temp = explode(" ", $name);
		$lname = $temp[count($temp) - 1]; //Get the last word for the last name
		$fname = implode(explode(" ", $name, -1)); //Re-stitch the words for the first name (but not the last name)

		$date = date("Y-m-d");
		$newdob = date("Y-m-d", strtotime($dob)); //Convert the DOB from HTML fields to SQL type

		$addresstotal = str_replace("\r\n", ", ", $address) . ", " . $postcode; //
		$addresstotal = str_replace("\n", ", ", $addresstotal);
		$addresstotal = str_replace(" ,", ",", $addresstotal); //Fix the damn annoying extra spaces that are added per line

		$refno = genRefNo();


		$phone = str_replace(" ", "", $phone); //Cut out spaces from phone numbers
		$phonemobile = str_replace(" ", "", $phonemobile); //Same as above
		$ecphone = str_replace(" ", "", $ecphone);

		$fname = $database->real_escape_string($fname); //Escape all strings to prevent SQL injections
		$lname = $database->real_escape_string($lname);
		$addresstotal = $database->real_escape_string($addresstotal);
		$food = $database->real_escape_string(filterNil($food));
		$ecname = $database->real_escape_string($ecname);
		$ecphone = $database->real_escape_string($ecphone);
		$medical = $database->real_escape_string(filterNil($medical));
		$phone = $database->real_escape_string($phone);
		$phonemobile = $database->real_escape_string($phonemobile);
		$email = $database->real_escape_string($email);

		$insertSQL = "INSERT INTO $TABLE_REGISTRATIONS (FirstName, LastName, DOB, Email, Address, Phone, MobilePhone, CompanyUnit, ContactName, ContactPhone," .
			"MedicalDetails, FoodDetails, RegisteeType, RefNo, CadetID, DateRegistered, PhotoPerm) VALUES ('$fname', '$lname', '$newdob', '$email'," .
			"'$addresstotal', '$phone', '$phonemobile', $company, '$ecname', '$ecphone', '$medical', '$food', $type, '$refno', '$agentID', '$date', $photoPerm);";

		$checkSQL = "SELECT Refno FROM $TABLE_REGISTRATIONS WHERE RefNo = '$refno'";

		$i = 0;
		while (runQuery($checkSQL)->num_rows > 0) {
			if ($i > 40) {
				debug("Error: Could not generate refno that does not already exist!");
				return;
			}

			$refno = genRefNo();
			$i++;
		}


		if ($database->query($insertSQL)) {
			debug("Inserted query.");

            $regType = ""; //This is the suffix for the file we will open
			if ($type == 1) $regType = "boy";
			else if ($type == 2) $regType = "parent_help";
			else if ($type == 3) $regType = "leader";
			else $regType = "other";

			$file = "register_$regType.txt"; //The file we are reading the email from

			$emailData = getEmailFile($file, array("name" => $fname, "refno" => $refno), false); //Read from file and interpret
			emailNoHTML($email, "Space Camp Registration", $emailData); //Send the email
            if ($DEBUG) {
                echo "<br><br>Email data:<br>" . $emailData;
            }
			session_start(); //Allows us to store the email they used while moving pages
			$_SESSION["display-email"] = $email; //Store it on 'display-email' so it doesn't collide with admin page login checks
			header("Location: thanks.php"); //Change to the thanks page
            exit();
		} else {
			debug("Failed to insert: " . $database->error);

			$data = "FirstName: $fname\r\n" . "LastName: $lname\r\n" . "DOB: $dob\r\n" . "Email: $email\r\n" . "Address: $addresstotal\r\n"
				. "Phone: $phone\r\n" . "MobilePhone: $phonemobile\r\n" . "Company (int): $company\r\n" . "Company: $companies[$company]\r\n"
				. "ContactName: $ecname\r\n" . "ContactPhone: $ecphone\r\n" . "Medical Details: $medical\r\n" . "Food Details: $food\r\n"
				. "Type: " . $type . "\r\n" . "RefNo: $refno\r\n" . "CadetID: $agentID\r\n"
				. "Date: $date\r\nPhotoPerm: $photoPerm\r\n";
			sendErrorEmail($data, $database->error);
			header("Location: error.php");
			exit();
		}

		//$database->close();
	} catch (PDOException $e) {
		debug("Failed with exception: " . $e->getMessage());
	}
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	foreach ($_POST as $key => $value)
	    if ($DEBUG) {
	        echo "Field ".htmlspecialchars($key)." is ".htmlspecialchars($value)."<br>";
	    }
		
	
	$name = $_POST["name"];
	$email = $_POST["email"];
	$address = $_POST["address"];
	$dob = $_POST["dateofbirth"];
	$company = $_POST["company"];
	
	$postcode = $_POST["postcode"];
	$food = $_POST["food"];
	$medical = $_POST["medical"];
	$phone = $_POST["phone"];
	$phonemobile = $_POST["phonemobile"];
	$agentID = $_POST["cadetid"];
	$ecname = $_POST["ecname"];
	$ecphone = $_POST["ecphone"];
	$personType = $_POST["type"];
	$photoPerm = isset($_POST["photoperm"]) ? 1 : 0; //For some reason, isset is not ever returning false so we have to specifiy it


	$response = $_POST["g-recaptcha-response"];
	
	
	if (!preg_match("/\w.*\s.*\w/", $name) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($address) || empty($dob) || empty($postcode) || (empty($phone) ||
            empty($phonemobile)) || empty($agentID) || empty($ecname) || empty($ecname) || !isset($companies[$company])) {
				$validating = true;

				if ($DEBUG) {
				    echo empty($name) . "-" . empty($email) . "-" . empty($email) . "-" . empty($dob) . "-" . empty($phone) . "-";
					echo empty($phonemobile) . "-" . empty($agentID) . "-" . empty($ecname) . "-" . !isset($companies[$company]);
				    /*if (isset($companies[$company])) {
				        echo "TRUE";
                    } if (!isset($companies[$company])) {
				        echo "FALSE";
                    }
				    echo isset($companies[$company]) . " = " . $company;*/
                }
	} else {
		//The form is valid. Register the user.
	    if (!checkCaptcha($response)) {
	        $captchaValid = false;
	    } else {
	        register($name, $dob, $agentID, $address, $postcode, $phone, $phonemobile, $email, $company, $food, $medical, $ecname, $ecphone, $personType, $photoPerm);
	    }
	}
	
	if ($DEBUG) {
	    echo "Company : " . ($company);
	    echo "Address: " . str_replace("\n", ", ", str_replace(" \n", ", ", $address));
	}
	
}

if (empty($agentID)) {
	$part1 = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 2);
	$part2 = substr(str_shuffle("00112233445566778899"), 0, 3);
	
	$agentID = $part1 . $part2;
}
//1 = email
//2 = name
//3 = company

function validate($input, $type = 0) {
    global $companies;
	if (!$GLOBALS['validating']) {
		echo "class=\"form\"";
		return;
	}

	$output = "class=\"form ";
	if ($type == 1 && !filter_var($input, FILTER_VALIDATE_EMAIL)) {
		$output = $output . "invalid-email";//Append 
	} else if ($type == 2 && !preg_match("/\w.*\s.*\w/", $input)) {
		$output = $output . "invalid-name";
	} else if ($type == 3 && !isset($companies[$input])) {
		$output = $output . "invalid-company";
	}else if ($input == null || $input == "") {
		$output = $output . "invalid";
	} 
	
	$output = $output . "\"";
	
	
	echo $output;
}

/**
 * Gets rid of any input that doesn't need to be there. E.g. 'nil', "nothing", etc
 * @param $input The input
 * @return string The input, but filtered
 */
function filterNil($input) {
    $filter = array("nil", "nothing", "none", "na", "n/a", "-", "null", "same as above", "see above", "no");
    for ($i = 0; $i < count($filter); $i++) {
        if (strcasecmp(trim($input), $filter[$i]) == 0) {
            return "";
        }
    }
    return $input;
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


//echo implode(", ", $array);
?>
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
    
    <title>Space Camp - Register</title>

    <!-- Bootstrap Core CSS -->
    <!-- <link href="css/bootstrap.min.css" rel="stylesheet"> -->

    <!-- Custom CSS -->
   	<link href="css/register.css" rel="stylesheet">
    
    <link rel="shortcut icon" href="favicon.ico">
    
    <script src="js/jquery.min.js"></script>
    <script src="js/register.js"></script>
    <script src="js/modernizr-custom.js"></script>

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
		<h1>Register for Space Camp</h1>
        <div id="register-form">
        	<!--  <div class="offset-xl-3 col-xl-6 offset-lg-3 col-lg-6 offset-md-1 col-md-10">-->
        	
        	<!-- <img id="register_img" src="../img/register.png">-->
        	<form id="register_form_form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        		<div class="form-group">
    	        	<input id="form-firstname" <?php validate($name, 2)?> name="name" placeholder="John Smith" type="text" maxlength="40" value="<?php echo $name?>">
    	        	<!--  <?php validate(1)?>
    	        	<input id="form-lastname" class="form" name="lname" placeholder="Smith" type="text" value="">
    				-->
    				<input id="form-agentID" class="form" disabled name="cadetid" type="text" maxlength="5" value="<?php echo $agentID?>">
    	        	
    	        	<select id="form-company" <?php validate($company, 3)?> name="company"  value="<?php echo $company?>">
    		        	<option value="0" disabled selected>** Please Select **</option>
    		        	<?php         							
    		        		foreach ($companies as $key => $value) {
    		        			echo "<option value='$key' " . ($company == $key ? "selected" : "") . ">" . $value . "</option>";
    		        		}
    		        		?>
    	        	</select>
    	        	
    	        	<select id="form-type" class="form" name="type" value="<?php echo $personType ?>">
                        <option value="1" <?php if ($personType == 1) echo "selected"; ?>>Boy</option>
                        <option value="2" <?php if ($personType == 2) echo "selected"; ?>>Parent Help</option>
                        <option value="3" <?php if ($personType == 3) echo "selected"; ?>>Officer/Leader</option>
                        <option value="4" <?php if ($personType == 4) echo "selected"; ?>>Other</option>
                    </select>
    	        	
    	        	<input id="form-dob" <?php validate($dob)?> name="dateofbirth" placeholder="dd/mm/yyyy" value="<?php echo $dob?>" type="date">
    				
    	        	<input id="form-email" <?php validate($email, 1)?> name="email" placeholder="example@domain.com" type="text" maxlength="50" value="<?php echo $email?>">
    	        	<input id="form-phone" <?php validate($phone)?> name="phone" type="text" value="<?php echo $phone?>" maxlength="15">
    	        	<input id="form-phonemobile" <?php validate($phonemobile)?> name="phonemobile" type="text" value="<?php echo $phonemobile?>" maxlength="15">
    	        	
    	        	<textarea cols="3" id="form-address" <?php validate($address)?>  name="address" placeholder="99 Example Drive" maxlength="60" type="text"><?php echo $address?></textarea>
    	        	
    	        	<input id="form-postcode" <?php validate($postcode)?> name="postcode" placeholder="0000" max="9999" maxlength="4" type="number" value="<?php echo $postcode?>">
    	        	
    	        	<textarea id="form-medical" <?php validate("-")?>  name="medical" placeholder="" maxlength="1024" type="text"><?php echo $medical?></textarea>
    	        	<textarea id="form-food" <?php validate("-")?>  name="food" placeholder="" maxlength="512" type="text"><?php echo $food?></textarea>
    	        	
    	        	<input id="form-ecname" <?php validate($ecname)?> placeholder="Mr. Smith" name="ecname" maxlength="20" type="text" value="<?php echo $ecname?>">
    	        	<input id="form-ecphone" <?php validate($ecphone)?> name="ecphone" type="text" maxlength="15" value="<?php echo $ecphone?>">
    	        	<input id="form-photo" name="photoperm" type="checkbox" <?php if ($photoPerm == 1) echo "checked" ?>>

    	        	<?php if ($validating) { //The form isn't valid still, or we will have been redirected
    	        		echo "<span id='form-invalid'>Please fill out all the fields marked in red, then try again.</span>";
    	        	}
    	        	
    	        	if (!$captchaValid) { //The captcha failed to verify them
    	        	    echo "<span id='form-invalid'>The captcha failed to verify you. Please try submitting again.</span>";
    	        	}
    	        	?>
    	        	
    	        	<!-- The button that is for the re-captcha -->
    	        	<button id="re-captcha--" class="g-recaptcha" data-sitekey="6LeZUCkUAAAAALIvQt51RFJhAZt2upmJo2tNApqz" data-callback="submitForm"> Submit </button>
	        	
	        	</div>
        	</form>
        		<!--</div>-->
        
       
    </div>
     <!-- Page Content -->
    
</body>
</html>
