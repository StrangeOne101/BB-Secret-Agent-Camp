<?php
if (!(isset($open) && $open)) {
	header("HTTP/1.1 403 Forbidden"); //Prevent it from being seen in a browser
	exit;
}


include('debug.php');

$ready = false;
$database = null;

$TABLE_LOGINS = "tbl_logins";
$TABLE_LOGINS_PENDING = "tbl_logins_pending"; //Logins that haven't been accepted yet
$TABLE_TOKENS = "tbl_tokens"; //The different tokens that can be used to modify SQL, along with the user who can do that
$TABLE_COMPANIES = "tbl_companies";
$TABLE_TYPES = "tbl_registee_types"; //Type of person registering. Either Child, Parent Help or Leader
$TABLE_REGISTRATIONS = "tbl_signups_" . (date("Y") - 2000); //tbl_signups_18, etc
$TABLE_CHANGES = "tbl_changes"; //To log all changes to any database
$TABLE_PAYMENTS = "tbl_payments"; //Payments and ID of all owing or payed campers/companies. Company IDs are their normal ID * -1

/**
 * Reads the ini file that contains the database connection
 * data. If it's not empty, the database should be attempted
 * to be connected to.
 * @return string The error message, or blank if it is ready
 */
function isDBDataReady() {


    if (findConfig() == null) {
    	debug("Could not find ini config file!");
    	return false;
	}

	$ini_array = parse_ini_file(findConfig());

    if (!isset($ini_array["port"])) {
        $ini_array["port"] = 1443;
    }

    $return = "";

    if (!isset($ini_array["hostname"])) {
        $return .= "Hostname not found;";
    }
    if (!isset($ini_array["username"])) {
        $return .= "Username not found;";
    }
    if (!isset($ini_array["databasename"])) {
        $return .= "Database name not found;";
    }

    return $return;
}

/**
 * Finds the location of the config.ini file for the database. The relative path can change
 * depending on the source of the original script, which is why this looks it up.
 * @return null|string The path. Will be null if not found.
 */
function findConfig() {
	$ini_array = null;
	$inipath = "config/database.ini";
	$pathAdditions = 2;

	while (!file_exists($inipath) && $pathAdditions > 0) { //This loop keeps looking up a directory until we find it
		$inipath = "../" . $inipath;
		$pathAdditions--;
	}
	try {
		$ini_array = parse_ini_file($inipath);
	} catch (Exception $e) {
		return null;
	}

	return $inipath;
}

/**
 * Checks the database to see if the table exists or not
 * @param string $tablename The table name
 * @return boolean True if the table exists.
 */
function tableExists($tablename) {
    global $database;
    $table_exists = "SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '$tablename'"; //Create SQL query
    $result = $database->query($table_exists)->num_rows; //Query the DB and store the return
    return $result > 0; //If there is more than one row, the table exists.
}

if (isDBDataReady() == "" && $database == null) { //If there are no issues AND the database has not been connected to yet
    try {
        $ini_array = parse_ini_file(findConfig()); //Read ini file
        $database = new mysqli($ini_array["hostname"], $ini_array["username"],
            $ini_array["password"], $ini_array["databasename"]); //Create new DB instance

        if ($database->connect_error) {
            debug("Database failed to connect: " . $database->connect_error);
            $database = null; //Set it back to null so we can attempt a connection again whenever another lib imports this DB script
        } else {
            debug("Connection to database made");
            $ready = true;

            //Login details of admins
            if (!tableExists($TABLE_LOGINS)) {
                $query = "CREATE TABLE $TABLE_LOGINS (`UserID` INTEGER UNSIGNED PRIMARY KEY AUTO_INCREMENT, `Email` VARCHAR(50), `Password` VARCHAR(255), `FirstName` VARCHAR(20), `LastName` VARCHAR(30), `Permission` TINYINT UNSIGNED DEFAULT 0)";
                $database->query($query);
                debug("Table '$TABLE_LOGINS' created in the database.");
                createLogin("root@localhost", "root1883", "Admin", "(Root Account)", 255);
            }

            if (!tableExists($TABLE_LOGINS_PENDING)) {
                $query = "CREATE TABLE $TABLE_LOGINS_PENDING (`TempUserID` INTEGER UNSIGNED PRIMARY KEY AUTO_INCREMENT, `Token` VARCHAR(255), `Email` VARCHAR(32), `FirstName` VARCHAR(20), `LastName` VARCHAR(30), `Permission` TINYINT UNSIGNED DEFAULT 0, `Expiry` DATETIME)";
                $database->query($query);
                debug("Table '$TABLE_LOGINS_PENDING' created in the database.");
            }

            //Tokens table. Used to store valid SQL queries that data editors stored in the logins table can run.
            if (!tableExists($TABLE_TOKENS)) {
                //$query = "CREATE TABLE $TABLE_TOKENS (`UserID` INTEGER UNSIGNED PRIMARY KEY, `ReadQuery` VARCHAR(512), `WriteQuery` VARCHAR(255))";
				$query = "CREATE TABLE $TABLE_TOKENS (`UserID` INTEGER UNSIGNED PRIMARY KEY, `Token` VARCHAR(64), `QueryID` INTEGER UNSIGNED, `Paramaters` VARCHAR(255))";
				$database->query($query);
                debug("Table '$TABLE_TOKENS' created in the database.");
            }

            //Table of all companies with basic IDs attached to them. For data saving purposes
            if (!tableExists($TABLE_COMPANIES)) { //Create the base table and insert all companies into it
                $query = "CREATE TABLE $TABLE_COMPANIES (`CompanyID` INTEGER UNSIGNED PRIMARY KEY AUTO_INCREMENT, `CompanyName` VARCHAR(40), `PayingAsCompany` BOOLEAN DEFAULT FALSE)";
                $database->query($query);
                $query = "INSERT INTO $TABLE_COMPANIES (`CompanyName`) VALUES ('1st Ashburton'), ('1st Blenheim'), ('1st Christchurch'), ('2nd Christchurch'), ('4th Christchurch'), ('8th Christchurch'), ('14th Christchurch'), ('1st Rangiora'), ('2st Rangiora'), "
                    . "('3rd Timaru'), ('1st Waimate'), ('Hornby ICONZ'), ('Lincoln ICONZ'), ('Oxford ICONZ'), ('Richmond ICONZ'), ('St Albans ICONZ'), ('Parklands ICONZ'), ('Westside ICONZ')";
                $database->query($query);
                $query = "INSERT INTO $TABLE_COMPANIES (`CompanyID`, `CompanyName`) VALUES (99, 'Other')";
                $database->query($query);
                debug("Table '$TABLE_COMPANIES' created in the database.");
            }

            //The different types of registee types
            if (!tableExists($TABLE_TYPES)) {
                $query = "CREATE TABLE $TABLE_TYPES (`TypeID` TINYINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, `TypeName` VARCHAR(20))";
                $database->query($query);
                $query = "INSERT INTO $TABLE_TYPES (`TypeName`) VALUES ('Child'), ('Parent Help'), ('Leader'), ('Other')";
                $database->query($query);
                debug("Table '$TABLE_TYPES' created in the database.");
            }

            //A table of all changes that will occur in this database
            if (!tableExists($TABLE_CHANGES)) {
                $query = "CREATE TABLE $TABLE_CHANGES (`ID` INTEGER UNSIGNED PRIMARY KEY AUTO_INCREMENT, `TableName` VARCHAR(30), `FieldName` VARCHAR(20), `ValueBefore` VARCHAR(512), `ValueAfter` VARCHAR(512), `ChangeTime` DATETIME)";
                $database->query($query);
                debug("Table '$TABLE_CHANGES' created in the database.");
            }

            //Registrations table
            if (!tableExists($TABLE_REGISTRATIONS)) {
                $query = "CREATE TABLE $TABLE_REGISTRATIONS (`ID` INTEGER UNSIGNED AUTO_INCREMENT PRIMARY KEY, `FirstName` VARCHAR(20) NOT NULL, 
		`LastName` VARCHAR(20) NOT NULL, `DOB` DATE NOT NULL, `CompanyUnit` TINYINT NOT NULL, `Email` VARCHAR(50) NOT NULL, 
		`Address` VARCHAR(60), `Phone` VARCHAR(14), `MobilePhone` VARCHAR(14), `ContactName` VARCHAR(30), `ContactPhone` VARCHAR(14), `MedicalDetails` VARCHAR(1024), `FoodDetails` VARCHAR(512),
		`RegisteeType` TINYINT DEFAULT 0, `DateRegistered` DATE, `PhotoPerm` BOOLEAN DEFAULT TRUE, `CadetID` VARCHAR(30), `RefNo` VARCHAR(12), `DatePaid` DATE DEFAULT NULL)";$database->query($query);
                debug("Table '$TABLE_REGISTRATIONS' created in the database.");
            }

        }

        global $database;
        $GLOBALS["database"] = $database; //Make the database variable global so other modules can use it
    } catch (PDOException $e) {
        debug("An error occured while accessing the database: <br>" . $e->getMessage());
    } catch (\Exception $e2) {
        debug("An error occured while trying to connect to the database: <br>" . $e2->getMessage());
    }
} else {
    debug(isDBDataReady());
}

function isReady() {
    global $ready;
    return $ready;
}

/**
 * Checks if the login is valid. Hashes the password and checks it.
 * @param string $email The username
 * @param string $password The password. Should not be pre-hashed.
 * @return boolean If the login is valid.
 */
function isValidLogin($email, $password) {
    global $database, $TABLE_LOGINS;
    $email = $database->real_escape_string(strtolower($email)); //Prevent SQL injections
    // $password = $database->real_escape_string(password_hash($password, PASSWORD_DEFAULT)); //Hash password then encode all sql chars

    $query = "SELECT `Password` FROM $TABLE_LOGINS WHERE `Email` = '$email'";
    $result = $database->query($query);
    if ($result) {
        debug("Generated password: " . $password);
        return $result->num_rows > 0 && password_verify($password, $result->fetch_assoc()["Password"]);
    } else {
        debug("Error occurred while checking login: " . $database->error);
    }
    return false;
}

/**
 * Checks the provided view token against the database
 * @param $token The token
 * @return bool If the token is valid or not
 */
function isValidToken($token) {
    global $database, $TABLE_LOGINS;
    $token = $database->real_escape_string($token);
    $query = "SELECT `Password` FROM $TABLE_LOGINS WHERE `Password` = '$token'";

    $result = $database->query($query);
    if ($result) {
        return $result->num_rows > 0;
    } else {
        debug("Error occurred while checking valid token: " . $database->error);
        return false;
    }
}

/**
 * Return the permission level of the given user.
 * @param string $email The username of the user
 * @return number The permission. Between 0 and 255, or -1 if not found.
 */
function getPermission($email) {
    global $database, $TABLE_LOGINS;
    $email = $database->real_escape_string(strtolower($email));

    $query = "SELECT Permission FROM $TABLE_LOGINS WHERE `Email` = '$email'";
    $result = $database->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return intval($row["Permission"]);
    } else {
        return -1;
    }
}

/**
 * Gets the name of the user
 * @param string $email The username of the user
 * @return string[]|NULL An array of the name of the user (first, last), or null if not found
 */
function getName($email) {
    global $database, $TABLE_LOGINS;
    $email = $database->real_escape_string(strtolower($email));

    $query = "SELECT `FirstName`, `LastName` FROM $TABLE_LOGINS WHERE `Email` = '$email'";
    $result = $database->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return array($row["FirstName"], $row["LastName"]);
    } else {
        return null;
    }
}

/**
 * Checks if the passed token is valid or not, and if it is, return the
 * query that it can use. Returns null otherwise.
 * @param string $token The token
 * @return The query, or null
 */
function getQueryFromToken($token) {
    global $database, $TABLE_LOGINS, $TABLE_TOKENS;

    $token = $database->real_escape_string($token); //To prevent SQL injections
    $query = "SELECT $TABLE_TOKENS.Query FROM `$TABLE_TOKENS` INNER JOIN `$TABLE_LOGINS` ON $TABLE_TOKENS.UserID = $TABLE_LOGINS.UserID WHERE $TABLE_LOGINS.Password = \"$token\"";
    $result = $database->query($query);
    if ($result->num_rows > 0) {
        return $result->fetch_assoc()["Query"];
    }
    return null;
}

/**
 * Runs an SQL query. Warning that there are no protective checks in this method.
 * @param $query The SQL query
 * @return mixed An SQL object, or a string of the error.
 */
function runQuery($query) {
    global $database;
    $result = $database->query($query);
    if ($result) {
        return $result;
    }
    return $database->error;
}

function getLastError() {
    global $database;
    return $database->error;
}

/**
 * Creates a new login.
 * @param string $email The username
 * @param string $password The password. Should not be pre-hashed.
 * @param string $firstname The user's first name
 * @param string $lastname The user's last name
 * @param integer $perms The permission value. Should be between 0 and 255 (inclusive)
 * @return boolean If it was successful. Failures will also output the error as debug.
 */
function createLogin($email, $password, $firstname, $lastname, $perms) {
    global $database, $TABLE_LOGINS;

    $email = $database->real_escape_string(strtolower($email)); //Prevent SQL injections
    $password = $database->real_escape_string(password_hash($password, PASSWORD_DEFAULT)); //Hash password then encode all sql chars
    $firstname = $database->real_escape_string($firstname);
    $lastname = $database->real_escape_string($lastname);
    $query = "INSERT INTO $TABLE_LOGINS (`Email`, `Password`, `FirstName`, `LastName`, `Permission`) VALUES ('$email', '$password', '$firstname', '$lastname', $perms)";

    if ($database->query($query)) return true;
    else {
        debug("Failed to create login: " . $database->error);
        return false;
    }
}



?>