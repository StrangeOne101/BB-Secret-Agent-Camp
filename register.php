<?php

if(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] != "on" && $_SERVER["HTTP_HOST"] != "localhost")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}

$open = true;
global $open;

include_once($_SERVER['DOCUMENT_ROOT'] . "/scripts/debug.php");

global $debugVal;
$debugVal = false;

include_once($_SERVER['DOCUMENT_ROOT'] . '/scripts/database.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/scripts/commonqueries.php');
include_once($_SERVER['DOCUMENT_ROOT'] . '/scripts/emails.php');

$fname = $lname = $email = $address = $postcode = $food = $medical = $phone = $phonemobile = $agentID = "";
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

global $captchaValid, $validating, $fname, $lname, $address, $postcode, $company, $dob, $email, $food, $medical, $phone, $phonemobile, $agentID, $ecname, $ecphone, $personType, $photoPerm;

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
$companiesById = array();
$companiesByOrder = array();
$companiesPayToCompany = array();

for ($i = 0; $i < $result->num_rows; $i++) {
    $currentRow = $result->fetch_assoc();
    $companiesById[$currentRow["CompanyID"]] = $currentRow["CompanyName"];
    if ($currentRow["Order"] != -1) $companiesByOrder[$currentRow["Order"]] = $currentRow["CompanyID"]; //Don't show disabled companies
    $companiesPayToCompany[$currentRow["CompanyID"]] = $currentRow["PayingAsCompany"] == 1 ? true : false;

    if ($DEBUG) {
        //echo $currentRow["CompanyID"] . " => " . $currentRow["CompanyName"] . "\n";
    }
}



if ($DEBUG) {
    //echo "<br>" . isset($companiesById["1"]) . " | " . isset($companiesById[1]);
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
 * Checks the database to see if the email previously exists
 * @param $email string The email
 * @return bool|string False if it doesn't exist, or the reference number if it does
 */
function email_exists_in_db($email) {
    global $TABLE_REGISTRATIONS;
    $SQL = "SELECT RefNo FROM $TABLE_REGISTRATIONS WHERE Email = '$email'";

    $result = runQuery($SQL);

    if ($result->num_rows > 0) {
        return$result->fetch_assoc()["RefNo"];
    }

    return false;
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
function register($fname, $lname, $dob, $agentID, $address, $postcode, $phone, $phonemobile, $email, $company, $food, $medical, $ecname, $ecphone, $type, $photoPerm) {
    try {

        global $TABLE_REGISTRATIONS, $database, $companiesById, $companiesPayToCompany, $DEBUG;

        //This is for splitting names into first name and last name when we have a combined field. This year we don't, so it's deprecated
        /*$temp = explode(" ", $name);
        $lname = $temp[count($temp) - 1]; //Get the last word for the last name
        $fname = implode(explode(" ", $name, -1)); //Re-stitch the words for the first name (but not the last name)*/

        $date = date("Y-m-d");
        $newdob = date("Y-m-d", strtotime($dob)); //Convert the DOB from HTML fields to SQL type

        $addresstotal = str_replace("\r\n", ", ", $address) . (empty($postcode) ? '' : ", " . $postcode); //
        $addresstotal = str_replace("\n", ", ", $addresstotal);
        $addresstotal = str_replace(" ,", ",", $addresstotal); //Fix the damn annoying extra spaces that are added per line

        $refno = email_exists_in_db("$email");

        if ($refno == false) $refno = genRefNo();


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

        $checkSQL = "SELECT Refno FROM $TABLE_REGISTRATIONS WHERE RefNo = '$refno' AND Email != '$email'";

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

            if ($companiesPayToCompany[$company] == true) { //If they are paying as a company, append the prefix for those type of emails
                $regType .= "_companypay";
            }

            $file = "register_$regType.txt"; //The file we are reading the email from

            $emailData = getEmailFile($file, array("name" => $fname, "refno" => $refno), true); //Read from file and interpret
            //emailNoHTML($email, "Space Camp Registration", $emailData); //Send the email
            emailSMTP($type >= 4 ? "info@copsandrobberscamp.co.nz" : $email, "Cops and Robbers Camp Registration", $emailData);
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
                . "Phone: $phone\r\n" . "MobilePhone: $phonemobile\r\n" . "Company (int): $company\r\n" . "Company: $companiesById[$company]\r\n"
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
    foreach ($_POST as $key => $value) {
        if ($DEBUG) {
            echo "Field " . htmlspecialchars($key) . " is " . htmlspecialchars($value) . "<br>";
        }
    }


    $fname = $_POST["firstname"];
    $lname = $_POST["lastname"];
    $email = $_POST["email"];
    $address = $_POST["address"];
    $dob = $_POST["dateofbirth"];
    $company = isset($_POST["company"]) ? $_POST["company"] : 0;

    $postcode = isset($_POST["postcode"]) ?$_POST["postcode"] : '';
    $food = $_POST["food"];
    $medical = $_POST["medical"];
    $phone = $_POST["phone"];
    $phonemobile = $_POST["phonemobile"];
    $agentID = $_POST["cadetid"];
    $ecname = $_POST["ecname"];
    $ecphone = $_POST["ecphone"];
    $personType = $_POST["type"];
    $photoPerm = isset($_POST["photoperm"]) ? 1 : 0; //For some reason, isset is not ever returning false so we have to specifiy it

    $name = $fname . ' ' . $lname;

    $response = $_POST["g-recaptcha-response"];


    if (!preg_match("/\w.*\s.*\w/", $name) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($address) || empty($dob) || (empty($phone) ||
            empty($phonemobile)) || empty($agentID) || empty($ecname) || empty($ecphone) || !isset($companiesById[$company]) || empty($fname)|| empty($lname)) {
        $validating = true;

        if ($DEBUG) {
            echo empty($name) . "-" . empty($email) . "-" . empty($email) . "-" . empty($dob) . "-" . empty($phone) . "-";
            echo empty($phonemobile) . "-" . empty($agentID) . "-" . empty($ecname) . "-" . !isset($companiesById[$company]);
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
            session_start();
            $_SESSION["require_payment"] = $companiesPayToCompany[$company];
            $_SESSION["carryover_lastname"] = $lname;
            $_SESSION["carryover_email"] = $email;
            $_SESSION["carryover_address"] = $address;
            $_SESSION["carryover_postcode"] = $postcode;
            $_SESSION["carryover_phone"] = $phone;
            $_SESSION["carryover_phonemo"] = $phonemobile;
            $_SESSION["carryover_econtact"] = $ecname;
            $_SESSION["carryover_ecphone"] = $ecphone;
            $_SESSION["carryover_company"] = $company;
            $_SESSION["last_type"] = $personType;

            register($fname, $lname, $dob, $agentID, $address, $postcode, $phone, $phonemobile, $email, $company, $food, $medical, $ecname, $ecphone, $personType, $photoPerm);
        }
    }

    if ($DEBUG) {
        echo "Company : " . ($company);
        echo "Address: " . str_replace("\n", ", ", str_replace(" \n", ", ", $address));
    }

} else if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["continue"])) {
        session_start();
        if (isset($_SESSION["carryover_email"])) {
            $lname = $_SESSION["carryover_lastname"];
            $email = $_SESSION["carryover_email"];
            $address = $_SESSION["carryover_address"];
            $postcode = $_SESSION["carryover_postcode"];
            $phone = $_SESSION["carryover_phone"];
            $phonemobile = $_SESSION["carryover_phonemo"];
            $ecname = $_SESSION["carryover_econtact"];
            $ecphone = $_SESSION["carryover_ecphone"];
            $company = $_SESSION["carryover_company"];
        }
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
    global $companiesById;
    if (!$GLOBALS['validating']) {
        echo "class=\"form-control\"";
        return;
    }

    $output = "class=\"form-control ";
    if ($type == 1 && !filter_var($input, FILTER_VALIDATE_EMAIL)) {
        $output = $output . "invalid-email is-invalid";//Append
    } else if ($type == 2 && !preg_match("/\w.*\s.*\w/", $input)) {
        $output = $output . "invalid-name is-invalid";
    } else if ($type == 3 && !isset($companiesById[$input])) {
        $output = $output . "invalid-company is-invalid";
    }else if ($input == null || $input == "") {
        $output = $output . "invalid is-invalid";
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
        'secret' => "6LdWuawUAAAAAFmyFnxmrX1kuVu0cSAFKloonZVl",
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
    <meta charset="UTF-8">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Toby Strange (StrangeOne101)">
    <title>Register | Cops and Robbers Camp</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="css/flier.css">
    <link rel="stylesheet" href="/css/register.css">
    <link rel="stylesheet" href="css/mobile.css">


    <script src="js/jquery.min.js"></script>
    <script src="js/register.js"></script>
    <script src="js/modernizr-custom.js"></script>

    <!-- Google's Captcha API - Prevents bots from flooding our DB -->
    <script src='https://www.google.com/recaptcha/api.js'></script>

    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
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

    <div id="header-logo">
        <h1>Register for</h1>
        <a href="index.php"><img id="title" src="/img/title.png" width="400px" /></a>

    </div>
    <div id="register-form">
        <form id="register_form_form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <?php if ($validating) { //The form isn't valid still, or we will have been redirected
                echo "<h5 id='form-invalid'>Please fill out all the fields marked in red, then try again.</h5>";
            }

            if (!$captchaValid) { //The captcha failed to verify them
                echo "<h5 id='form-invalid'>The captcha failed to verify you. Please try submitting again.</h5>";
            }?>

            <div class="form-group row">
                <div class="col col-lg-6">
                    <label for="form-firstname">First Name</label>
                    <input id="form-firstname" name="firstname" <?php validate($fname)?> placeholder="John" type="text" maxlength="40" value="<?php echo $fname?>">
                </div>
                <div class="col col-lg-6">
                    <label for="form-lastname">Last Name</label>
                    <input id="form-lastname" name="lastname" <?php validate($lname)?> placeholder="Smith" type="text" maxlength="40" value="<?php echo $lname?>">
                </div>
            </div>
            <div class="form-group row">
                <!--  <?php validate(1)?>
                -->
                <input id="form-agentID" class="form hidden" disabled name="cadetid" type="text" maxlength="5" value="<?php echo $agentID?>">

                <div class="col col-lg-8">
                    <label for="form-company">Company / Unit</label>
                    <select id="form-company" <?php validate($company, 3)?> name="company" value="<?php echo $company?>">
                        <option value="0" disabled selected>** Please Select **</option>
                        <?php
                        foreach ($companiesByOrder as $order => $id) {
                            $name = $companiesById[$id];
                            echo "<option value='$id' " . ($company == $id ? "selected" : "") . ">" . $name . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col col-lg-4">
                    <label for="form-type">Registee Type</label>
                    <select id="form-type" class="form-control" name="type" value="<?php echo $personType ?>">
                        <option value="1" <?php if ($personType == 1) echo "selected"; ?>>Boy</option>
                        <option value="2" <?php if ($personType == 2) echo "selected"; ?>>Parent Help</option>
                        <option value="3" <?php if ($personType == 3) echo "selected"; ?>>Officer/Leader</option>
                        <option value="4" <?php if ($personType == 4) echo "selected"; ?>>Other</option>
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <div class="col col-lg-8 col-xs-12">
                    <label for="form-email">Email</label>
                    <input id="form-email" <?php validate($email, 1)?> name="email" placeholder="example@domain.com" type="text" maxlength="50" value="<?php echo $email?>">
                </div>
                <div class="col col-lg-4 col-xs-12">
                    <label for="form-dob">Date of Birth</label>
                    <input id="form-dob" <?php validate($dob)?> name="dateofbirth" placeholder="dd/mm/yyyy" value="<?php echo $dob?>" type="date">
                </div>
            </div>
            <div class="form-group row">
                <div class="col col-lg-6">
                    <label for="form-phone">Phone Number</label>
                    <input id="form-phone" <?php validate($phone)?> name="phone" type="text" value="<?php echo $phone?>" maxlength="15">
                </div>
                <div class="col col-lg-6">
                    <label for="form-phonemobile">Mobile Number</label>
                    <input id="form-phonemobile" <?php validate($phonemobile)?> name="phonemobile" type="text" value="<?php echo $phonemobile?>" maxlength="15">

                </div>
            </div>
            <div class="form-group row">
                <div class="col col-lg-12">
                    <label for="form-address">Address</label>
                    <textarea cols="3" id="form-address" <?php validate($address)?>  name="address" placeholder="99 Example Drive, Suburb, 1234" maxlength="60" type="text"><?php echo $address?></textarea>
                </div>
            </div>
            <div class="form-group row">
                <div class="col col-lg-12">
                    <label for="form-medical">Medical Needs</label>
                    <textarea id="form-medical" <?php validate("-")?>  name="medical" placeholder="Diabetes, Epilepsy, Sleepwalking, etc" maxlength="1024" type="text"><?php echo $medical?></textarea>
                </div>
            </div>
            <div class="form-group row">
                <div class="col col-lg-12">
                    <label for="form-food">Dietary Requirements</label>
                    <textarea id="form-food" <?php validate("-")?>  name="food" placeholder="Allergies, Gluten free, Dairy free, etc" maxlength="512" type="text"><?php echo $food?></textarea>
                </div>
            </div>
            <div class="form-group row">
                <div class="col col-lg-6">
                    <label for="form-ecname">Emergency Contact Name</label>
                    <input id="form-ecname" <?php validate($ecname)?> placeholder="Mr. Smith" name="ecname" maxlength="20" type="text" value="<?php echo $ecname?>">

                </div>
                <div class="col col-lg-6">
                    <label for="form-ecphone">Emergency Contact Phone</label>
                    <input id="form-ecphone" <?php validate($ecphone)?> name="ecphone" type="text" maxlength="15" value="<?php echo $ecphone?>">

                </div>
            </div>
                <!--<input id="form-postcode" <?php validate($postcode)?> name="postcode" placeholder="0000" max="9999" maxlength="4" type="number" value="<?php echo $postcode?>">-->

            <div class="form-group row">
                <div class="col col-lg-11 col-xs-12" style="padding-right: 0px">
                    <h6>I give my consent for any photographs and/or video taken of my son during this event to be used in Boys’ Brigade/ICONZ publicity.</h6>
                </div>
                <div class="col col-lg-1 col-xs-12" style="padding-left: 0px">
                    <input id="form-photo" name="photoperm" type="checkbox" class="form-control" <?php if ($photoPerm == 1) echo "checked" ?>>
                </div>

            </div>

            <h6>By clicking submit I give permission for my son to attend the 2019 Team & Adventure Camp. I have listed his special medical requirements,
                agree that in an emergency those in charge can make any necessary decisions if we, his parents/guardians, cannot be contacted. We agree
                to pay any medical costs that may be incurred by my son during the camp. If a medical or behavioral condition, not disclosed above,
                impacts on my sons ability to participate at this event he will be returned home at my expense. To ensure that everyone has a good time
                at camp there is a total ban on all illicit items including party pills, alcohol, cigarettes & weapons. Where deemed necessary a search
                of belongings may be carried out should there be any reason presented. I understand that after registrations close on 30 July 2019 that
                I will not receive a refund should my son be unable to attend camp for any reason.</h6>
            <br><br>
            <!-- The button that is for the re-captcha -->
            <button id="re-captcha--" class="g-recaptcha" data-sitekey="6LdWuawUAAAAAEmCoRgh3bRKAiDk54ZCLpOl-zFV" data-callback="submitForm"> Submit </button>

            <h6></h6>

        </form>
    </div>

</body>
</html>