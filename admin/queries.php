<?php 

session_start();

$valid = false;

if (isset($_SESSION["pin"])) {
    $pin = $_SESSION["pin"];
    $valid = true;
    
    if (md5($pin) != "c45008212f7bdf6eab6050c2a564435a") {
        echo "Incorrect pin.<br>";
        $valid = false;
    } else {
        $title = $_SESSION["title"];
        $data = $_SESSION["data"];
        
        echo "<h1>$title</h1><br>";
        echo $data;
    } 
} else {
    echo "<h1>Database Queries</h1>";
    $medicalSQL = 'SELECT tbl_signups_17.FirstName, tbl_signups_17.LastName, tbl_companies.CompanyString, tbl_signups_17.MedicalDetails FROM `tbl_signups_17`
INNER JOIN `tbl_companies` ON tbl_signups_17.CompanyUnit=tbl_companies.CompanyUnit
WHERE tbl_signups_17.MedicalDetails != ""';
    
    $foodSQL = 'SELECT tbl_signups_17.FirstName, tbl_signups_17.LastName, tbl_companies.CompanyString, tbl_signups_17.FoodDetails FROM `tbl_signups_17`
INNER JOIN `tbl_companies` ON tbl_signups_17.CompanyUnit=tbl_companies.CompanyUnit
WHERE tbl_signups_17.FoodDetails != ""';
    
    
    echo '<input type="button" value="View all medical data">';
}

?>
