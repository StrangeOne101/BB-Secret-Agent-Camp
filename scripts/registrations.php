<?php
if (!(isset($open) && $open)) {
    header("HTTP/1.1 403 Forbidden"); //Prevent it from being seen in a browser
    exit;
}

include 'debug.php';
include 'database.php';

$table = "tbl_signups_" . (date("Y") - 2000);
$table_contacts = "tbl_contacts_" . (date("Y") - 2000);

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
$companies[99] = "Other";

$GLOBALS["companies"] = $companies;

function setupRegistrations() {
    $table_sql = "CREATE TABLE $table (`ID` SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, `FirstName` VARCHAR(20) NOT NULL,
		`LastName` VARCHAR(20) NOT NULL, `DOB` DATE NOT NULL, `CompanyUnit` TINYINT NOT NULL, `Email` VARCHAR(50) NOT NULL,
		`Address` VARCHAR(60), `Phone` VARCHAR(14), `MobilePhone` VARCHAR(14), `MedicalDetails` VARCHAR(1024), `FoodDetails` VARCHAR(512),
		`RegisteeType` TINYINT DEFAULT 0, `RefNo` VARCHAR(11), `DatePaid` DATE, `DateRegistered` DATE, `PhotoPerm` BOOLEAN DEFAULT TRUE)";
    
    if (!tableExists($table_sql)) {
        if ($GLOBALS["database"]->query($table_sql)) {
            debug("Table $table created successfully.");
        } else {
            debug("Error creating table in database: " . $conn->error);
            return false;
        }
    }
    
    $table_sql = "CREATE TABLE $table_contacts (`EContactID` SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, `FirstName` VARCHAR(20) NOT NULL,
        `LastName` VARCHAR(20) NOT NULL, `Email` VARCHAR(50), `Address` VARCHAR(60), `Phone` VARCHAR(14), `MobilePhone` VARCHAR(14), 
        `DateRegistered` DATE DEFAULT GETDATE()";
    
    if (!tableExists($table_sql)) {
        if ($GLOBALS["database"]->query($table_sql)) {
            debug("Table $table_contacts created successfully.");
        } else {
            debug("Error creating table in database: " . $conn->error);
            return false;
        }
    }
    return true;
}



?>