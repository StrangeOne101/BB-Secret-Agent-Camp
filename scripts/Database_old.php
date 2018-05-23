<?php
if (!(isset($open) && $open)) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}

include 'emails.php';

global $companies;
$companies = array();

$table = "tbl_signups_" . (date("Y") - 2000);
global $table;

$companies[0] = "1st Ashburton";
$companies[1] = "1st Blenheim";
$companies[2] = "1st Christchurch";
$companies[3] = "2nd Christchurch";
$companies[4] = "4th Christchurch";
$companies[5] = "8th Christchurch";
$companies[6] = "14th Christchurch";
$companies[7] = "1st Rangiora";
$companies[8] = "2st Rangiora";
$companies[9] = "3rd Timaru";
$companies[10] = "1st Waimate";
$companies[11] = "Hornby ICONZ";
$companies[12] = "Lincoln ICONZ";
$companies[13] = "Oxford ICONZ";
$companies[14] = "Richmond ICONZ";
$companies[15] = "St Albans ICONZ";
$companies[16] = "Parklands ICONZ";
$companies[17] = "Westside ICONZ";
$companies[18] = "Other";

$GLOBALS["companies"] = $companies;



function debug($string) {
    echo "<span class='debug'>" . $string . "</span>";
}

function setUpDB() {
	try {
		global $conn;
		
		$servername = "localhost";
		$username = "BBTeamSectionCmp";
		$password = "SecretAgentCamp17";
		$dbname = "bb_teamsectioncamp_db";
		
		$conn = new mysqli($servername, $username, $password, $dbname);
		// set the PDO error mode to exception
		//$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		//$sql = "CREATE DATABASE BB_SAC";
		//$conn->exec($sql);
		
		if ($conn->connect_error) {
			//die("Database failed to connect: " . $conn->connect_error . "<br>");
			debug("Database failed to connect: " . $conn->connect_error);
		} else {
		    debug("Database Connected");
		}
		
		$table = $GLOBALS["table"];
		
		debug("Checking table name again: $table<br>");
		
		$table_exists = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '$table'";
		$table_sql = "CREATE TABLE $table (`ID` SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, `FirstName` VARCHAR(20) NOT NULL, 
		`LastName` VARCHAR(20) NOT NULL, `DOB` DATE NOT NULL, `CompanyUnit` TINYINT NOT NULL, `Email` VARCHAR(50) NOT NULL, 
		`Address` VARCHAR(60), `Phone` VARCHAR(14), `MobilePhone` VARCHAR(14), `ContactName` VARCHAR(30), `ContactPhone` VARCHAR(14), `MedicalDetails` VARCHAR(1024), `FoodDetails` VARCHAR(512), `DetailsQuick` SMALLINT DEFAULT 0,
		`RegisteeType` TINYINT DEFAULT 0, `RefNo` VARCHAR(11), `DatePaid` DATE, `AgentID` VARCHAR(6), `DateRegistered` DATE, `PhotoPerm` BOOLEAN DEFAULT TRUE)";
		
		$result = $conn->query($table_exists)->num_rows;
		debug("Result $result");
		if ($result <= 0) {
			if ($conn->query($table_sql)) {
				debug("Database created successfully");
			} else {
			    debug("Error creating table in database: " . $conn->error);
			}
			
		} else {
		    debug("Table exists; not generating.");
		}
		
		
		
		debug("Database set up.");
	}
	catch(PDOException $e)
	{
	    debug("An error occured while accessing the database: <br>" . $e->getMessage());
	}
}

function overflow32($v)
{
	$v = $v % 4294967296;
	if ($v > 2147483647) return $v - 4294967296;
	elseif ($v < -2147483648) return $v + 4294967296;
	else return $v;
}

function hashCode( $s )
{
	$h = 0;
	$len = strlen($s);
	for($i = 0; $i < $len; $i++)
	{
		$h = overflow32(31 * $h + ord($s[$i]));
	}
	
	return $h;
}

function genRefNo() {
	$refno = "#BB";
	
	for ($int = 0; $int < 8; $int++) {
		$refno = $refno . rand(0, 9);
	}
	return $refno;
}

function register($name, $dob, $agentID, $address, $postcode, $phone, $phonemobile, $email, $company, $food, $medical, $ecname, $ecphone, $officer) {
	try {
		setUpDB();
		
		$temp = explode(" ", $name);
		$lname = $temp[count($temp) - 1];
		$fname = implode(explode(" ", $name, -1));
		
		$date = date("Y-m-d");
		$newdob = date("Y-m-d", strtotime($dob));
		
		$addresstotal = str_replace("\n", ",", $address) . ", " . $postcode;
		
		$registertype = $officer;
		
		$refno = genRefNo();
		
		$table = $GLOBALS["table"];
		$conn = $GLOBALS["conn"];
		
		$phone = str_replace(" ", "", $phone);
		$phonemobile = str_replace(" ", "", $phonemobile);
		$ecphone = str_replace(" ", "", $ecphone);
		
		debug( "Table name: $table");
		debug("Date: $date");
		
		$companystring = $GLOBALS['companies'][$company];
		
		$fname = $conn->real_escape_string($fname);
		$lname = $conn->real_escape_string($lname);
		$addresstotal = $conn->real_escape_string($addresstotal);
		$food = $conn->real_escape_string($food);
		$ecname = $conn->real_escape_string($ecname);
		$ecphone = $conn->real_escape_string($ecphone);
		$medical = $conn->real_escape_string($medical);
		$phone = $conn->real_escape_string($phone);
		$phonemobile = $conn->real_escape_string($phonemobile);
		$email = $conn->real_escape_string($email);
		
		$insertSQL = "INSERT INTO $table (FirstName, LastName, DOB, Email, Address, Phone, MobilePhone, CompanyUnit, ContactName, ContactPhone," . 
		"MedicalDetails, FoodDetails, RegisteeType, RefNo, AgentID, DateRegistered) VALUES ('$fname', '$lname', '$newdob', '$email'," . 
		"'$addresstotal', '$phone', '$phonemobile', $company, '$ecname', '$ecphone', '$medical', '$food', $registertype, '$refno', '$agentID', '$date');";
		
		$checkSQL = "SELECT RefNo FROM $table WHERE EXISTS(SELECT * FROM $table WHERE RefNo = '$refno')";
		
		$i = 0;
		while ($conn->query($checkSQL)->num_rows > 0) {
			if ($i > 40) {
			    debug("Error: Could not generate refno that does not already exist!");
				return;
			}
			
			$refno = genRefNo();
			$i++;
		}
		
		if ($conn->query($insertSQL)) {
		    debug("Inserted query.");
		    
		    if ($registertype == 1) {
		        sendLeaderEmail($email, $fname, $refno);
		    } else {
		        sendParentEmail($email, $fname, $refno);
		    }
		    session_start();
		    $_SESSION["email"] = $email;
		    header("Location: thanks.php");
		    
		} else {
		    debug("Failed to insert: " . $conn->error);
		    
		    $data = "FirstName: $fname\r\n" . "LastName: $lname\r\n" . "DOB: $dob\r\n" . "Email: $email\r\n" . "Address: $addresstotal\r\n"
		    . "Phone: $phone\r\n" . "MobilePhone: $phonemobile\r\n" . "Company (int): $company\r\n" . "Company: $companystring\r\n"
		    . "ContactName: $ecname\r\n" . "ContactPhone: $ecphone\r\n" . "Medical Details: $medical\r\n" . "Food Details: $food\r\n"
		    . "Leader: " . ($registertype == 1 ? "True" : "False") . " ($registertype)\r\n" . "RefNo: $refno\r\n" . "AgentID: $agentID\r\n"
		    . "Date: $date\r\n";
		    sendErrorEmail($data, $conn->error);
		    header("Location: error.php");
		}
		
		$conn->close();
	} catch (PDOException $e) {
	    debug("Failed with exception: " . $e->getMessage());
	}
}
?>