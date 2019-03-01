<?php
/**
 * Used to make a query to the database. Must be logged in with a session to access it.
 * Author: StrangeOne101 (Toby Strange)
 * Date: 06-May-18
 * Time: 9:04 PM
 */

//Prevent all users from seeing the page in the browser. It should only be called from AJax requests
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("HTTP/1.1 403 Forbidden");
    return;
}

//TODO: Update all PHP scripts to use JSON response instead of HTML

/***********************************
 *       Response Code Table       *
 *---------------------------------*
 * Code |         Meaning          *
 *----------------------------------
 * 200  | Response is fine         *
 * 210  | Hashcode response
 * 400  | Not enough info          *
 * 401  | Not authenticated        *
 * 432  | Query is not safe        *
 * 433  | Query error              *
 * 500  | DB is down               *
 ***********************************/

/**
 * Creates a JSON response with the appropriate error parameters
 * @param integer $errorcode The error code
 * @param string $errormessage The response error
 * @param array $extradata Any extra data to include in the response
 */
function reply_error($errorcode, $errormessage, $extradata = array()) {
	$data = array("response-code" => $errorcode, "message" => $errormessage);

	$data = array_merge($data, $extradata);
	header('Content-Type: application/json');
	echo json_encode($data);
}

/**
 * Creates a JSON response filled with the provided HTML table for the query
 * @param string $table The HTML table
 */
function reply_table($table) {
	$data = array("response-code" => 200, "message" => "", "data" => $table);
	header('Content-Type: application/json');
	echo json_encode($data);
}

/**
 * Creates a JSON response for database refresh checks
 * @param string $hashcode
 */
function reply_hashcode($hashcode) {
	$data = array("response-code" => 210, "message" => "", "hashcode" => $hashcode);
	header('Content-Type: application/json');
	echo json_encode($data);
}

session_start();
if (!(isset($_SESSION["email"])) && !isset($_SESSION["token"])) {
    reply_error(401, "Authentication failed");
    return;
}

$loggedIn = isset($_SESSION["email"]);

$open = true; //So the database doesn't complain that we are using it incorrectly and give us a 403
global $open;

include_once("../scripts/debug.php");

global $debugVal;
$debugVal = false; //So debug doesn't echo

include_once("../scripts/database.php");

global $database;

if (!isReady()) { //The database isn't functioning
    reply_error(500, "Database unavailable; data cannot be read");
    return;
}

if (!isset($_POST["query"]) && !isset($_POST["queryno"])) {
    reply_error(400, "No query provided");
    return;
}

function verifyTokenQuery() {
	global $TABLE_TOKENS, $database;
	$token = $_SESSION["token"];

	if (isset($_POST["query"])) return false; //If they are just asking for a non-common query, deny them
	$query = "SELECT * FROM $TABLE_TOKENS WHERE `Token` = '$token'";
	$result = $database->query($query);

	if (!$result) {
		return false; //This shouldn't really happen but oh well
	}

	$row = $result->fetch_assoc();
	$params = $row["Parameters"];
	$queryno = $row["QueryID"];


	//If there is required parameters and either none have been provided or they don't match, return false
	if ($params != "" && (!isset($_POST["parameters"]) || $_POST["parameters"] != $params)) return false;

	if ($queryno != $_POST["queryno"]) return false;

	return true; //Every other check has passed so return true
}

function validateQuery($query) {
    $query = explode(";", $query)[0]; //Only run the first query
    $query = trim($query);

    $firstWord = explode(" ", $query)[0];

    if (strtolower($firstWord) != "select" && strtolower($firstWord) != "update") {
        reply_error(432, "Invalid query. Query must be a SELECT query.");
        return false;
    }

    //TODO: Validate Query based on permissions if they are using a token
    //if (query.matches("(SELECT|UPDATE) (\*|\([\w,`.\\']{2,}\)) FROM tbl_signups_[0-9]{2} WHERE ([`']?)CompanyUnit([`']?) = $companyID .*")

    return true;
}



/**
 * Creates an HTML table based on the provided SQL query object. Does not
 * return the table but instead just echos it. This is because this PHP
 * script is made for HTTP requests and not for viewing directly or for
 * use as a script.
 * @param mixed $data The SQL query object
 * @return string The HTML table of the query
 */
function createTable($data) {

	$reply = "";

    $reply .= "<table class=\"table table-striped database-table\">";
	$reply .= "<thead>";
	$reply .= "<tr>";
	$reply .= "<th scope=\"col\">#</th>"; //The # is the name of the field for row number

    //Echo each field name in the table
    while ($field = mysqli_fetch_field($data)) {
		$reply .= "<th scope=\"col\">" . $field->name . "</th>";
    }

	$reply .= "</tr>";
	$reply .= "</thead>";
	$reply .= "<tbody>";

    $row_count = 0;
    $last_row = null;
    while($row = mysqli_fetch_assoc($data)) { //For every single row in the SQL table
        $row_count++;

		$reply .= "<tr>"; //Start row
		$reply .= "<td scope=\"row\">" . $row_count ."</td>"; //Echo the row number

        foreach ($row as $value) { //Go through each field and echo the value
			$reply .= "<td>" . $value . "</td>";
        }

		$reply .= "</tr>"; //End row
        $last_row = $row;
    }

	$reply .= "</tbody>";
	$reply .= "</table>";

	return $reply;
    //echo "<h3>Size of row: " . count($last_row) . "</h3>";
}

/**
 * Returns the database data in the form of a CSV file. This can be used for generic
 * use in HTML as well.
 * @param $data The MySQL data
 * @param $headers If the field names should be included or not
 */
function createCSV($data, $headers) {
    $fieldnames = "";
    $reply_data = "";

    //Echo the field names, if allowed
    if ($headers) {
        while ($field = mysqli_fetch_field($data)) {
            //Don't add the ',' if there is no previous field name in the string
            $fieldnames = $fieldnames == "" ? $field->name : $fieldnames . "," . $field->name;
        }
        $fieldnames .= "\r\n"; //Add a newline character
        $reply_data .= $fieldnames;
    }

    $row_count = 0;
    $csv_body = "";
    while($row = mysqli_fetch_assoc($data)) { //For every single row in the SQL table
        $row_count++;

        if ($row_count != 1) {
            $csv_body .= "\r\n"; //Add a newline before the start of a new row
        }

        $bool = false;
        foreach ($row as $value) {
        	$value = str_replace("\r\n", ", ", $value);
			$value = str_replace("\n", ", ", $value);
			if (strpos($value, ',') !== false) {
				$value = "\"" . $value . "\""; //Escape the value in quotes as it has commas in it
			}
            $csv_body .= !$bool ? $value : "," . $value; //Add each value from each column
            $bool = true; //Mark first column done, so start appending ', ' before each value now
        }
    }

	$reply_data .= $csv_body;
    return $reply_data;
}

if (!$loggedIn && !verifyTokenQuery()) {
	reply_error(401, "Unauthorized query."); //Query is not the one they are allowed to use
	return;
}

include_once("../scripts/commonqueries.php");

if (isset($_POST["query"])) {
    $query = $database->real_escape_string($_POST["query"]);
} else {
    $queryno = strval($_POST["queryno"]);
    /*if (count(explode("_", $queryno, 2)) > 1) { //If the query has a parameter
        $param = explode("_", $queryno, 2)[1];
        $queryno = explode("_", $queryno, 2)[0];
    }*/
    $param = isset($_POST["parameters"]) ? $_POST["parameters"] : "";

    if ($queryno == "0") $query = getRegistrationQuery();
    else if ($queryno == "1") {
        if (!isset($param) || $param == "" || $param == null) {
			reply_error(400, "No company parameter given");
            return;
        } else if (!intval($param)) {
            reply_error(400, "Company parameter must be an integer");
            return;
        }
        $query = getRegistrationsByCompanyQuery(intval($param));
    } else if ($queryno == "2") {
    	$query = getRecentRegistrations();
	} else if ($queryno == "3") {
		$query = getDietaryRegistrations();
	} else if ($queryno == "4") {
		$query = getMedicalRegistrations();
	} else {
		reply_error(400, "Unknown common query with ID $queryno");
        return;
    }


}

if (!validateQuery($query)) { //Check token permissions and query legitstics, etc
    return;
}
$data = runQuery($query);

if (is_string($data)) {
    reply_error(433, "Query Error: " . getLastError());
    return;
}


if (isset($_POST["notable"]) || isset($_POST["csv"])) { //Return in CSV format
    $reply = createCSV($data, !isset($_POST["noheaders"])); //No headers prevents the field names from being included

	reply_table($reply); //Reply in JSON
} else if (isset($_POST["refresh"])) {
	$rows = array();
	while($r = mysqli_fetch_assoc($data)) {
		$rows[] = $r;
	}
	$hash = md5(json_encode($rows));
	reply_hashcode($hash);
} else {
    $reply = createTable($data);

    reply_table($reply); //Reply in JSON.
}


//Done :D