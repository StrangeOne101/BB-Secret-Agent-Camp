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

session_start();
if (!(isset($_SESSION["email"]))) {
    echo "<h4>Error: Could not authenticate!</h4>";
    return;
}

$open = true; //So the database doesn't complain that we are using it incorrectly and give us a 403
global $open;

include("../scripts/debug.php");

global $debugVal;
$debugVal = false; //So debug doesn't echo

include("../scripts/database.php");

global $database;

if (!isReady()) { //The database isn't functioning
    echo "<h4>Error: The database isn't functioning right now! No data can be read.</h4>";
    return;
}

if (!isset($_POST["query"]) && !isset($_POST["queryno"])) {
    echo "<h4>Error: No query provided!</h4>";
    return;
}

function validateQuery($query) {
    $query = explode(";", $query)[0]; //Only run the first query
    $query = trim($query);

    $firstWord = explode(" ", $query)[0];

    if (strtolower($firstWord) != "select" && strtolower($firstWord) != "update") {
        echo "<h4>Error: Invalid query! Query must be a select query!</h4>";
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
 */
function createTable($data) {

    echo "<table class=\"table table-striped database-table\">";
    echo "<thead>";
    echo "<tr>";
    echo "<th scope=\"col\">#</th>"; //The # is the name of the field for row number

    //Echo each field name in the table
    while ($field = mysqli_fetch_field($data)) {
        echo "<th scope=\"col\">" . $field->name . "</th>";
    }

    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";

    $row_count = 0;
    $last_row = null;
    while($row = mysqli_fetch_assoc($data)) { //For every single row in the SQL table
        $row_count++;

        echo "<tr>"; //Start row
        echo "<td scope=\"row\">" . $row_count ."</td>"; //Echo the row number

        foreach ($row as $value) { //Go through each field and echo the value
            echo "<td>" . $value . "</td>";
        }

        echo "</tr>"; //End row
        $last_row = $row;
    }

    echo "</tbody>";
    echo "</table>";
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

    //Echo the field names, if allowed
    if ($headers) {
        while ($field = mysqli_fetch_field($data)) {
            //Don't add the ',' if there is no previous field name in the string
            $fieldnames = $fieldnames == "" ? $field->name : $fieldnames . "," . $field->name;
        }
        $fieldnames .= "\r\n"; //Add a newline character
        echo $fieldnames;
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
            $csv_body .= !$bool ? $value : "," . $value; //Add each value from each column
            $bool = true; //Mark first column done, so start appending ', ' before each value now
        }
    }

    echo $csv_body;
}

include("../scripts/commonqueries.php");

if (isset($_POST["query"])) {
    $query = $_POST["query"];
} else {
    $queryno = strval($_POST["queryno"]);
    if (count(explode("_", $queryno, 2)) > 1) { //If the query has a parameter
        $param = explode("_", $queryno, 2)[1];
        $queryno = explode("_", $queryno, 2)[0];
    }

    if ($queryno == "0") $query = getRegistrationQuery();
    else if ($queryno == "1") {
        if (!isset($param) || $param == "" || $param == null) {
            echo "<h4>Error: No company parameter given!</h4>";
            return;
        } else if (!intval($param)) {
            echo "<h4>Error: Company parameter must be an int!</h4>";
            return;
        }
        $query = getRegistrationsByCompanyQuery(intval($param));
    } else {
        echo "<h4>Error: Unknown common query with ID $queryno!</h4>";
        return;
    }


}

if (!validateQuery($query)) { //Check token permissions and query legitstics, etc
    return;
}
$data = runQuery($database->real_escape_string($query));

if (is_string($data)) {
    echo "<h4>Query Error: " . getLastError() . "</h4>";
    return;
}

if (isset($_POST["notable"]) || isset($_POST["csv"])) { //Return in CSV format
    createCSV($data, !isset($_POST["noheaders"])); //No headers prevents the field names from being included
} else {
    createTable($data);
}


//Done :D