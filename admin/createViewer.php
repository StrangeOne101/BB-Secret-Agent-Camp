<?php
/**
 * Created by PhpStorm.
 * Author: StrangeOne101 (Toby Strange)
 * Date: 19-Jul-18
 */
$open = true;
global $open;

include_once("../scripts/debug.php");
include_once("../scripts/database.php");
include_once("../scripts/tokens.php");

if (!isReady()) { //If the database is broken. If so... fek
	header("Location: index.php");
	exit();
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
	header("HTTP/1.1 403 Forbidden");
	exit();
}

session_start();
if (!(isset($_SESSION["email"]))) {
	echo "<h4>Error: Could not authenticate!</h4>";
	exit();
}

if (getPermission($_SESSION["email"]) < 128) { //So only people with a permission of 128 or higher can use this
	echo "<h4>Error: Do not have permission!</h4>";
	exit();
}

if (!isset($_POST["query"]) || !isset($_POST["title"])) {
	echo "<h4>Error: Invalid parameters provided in post request! Must specify both query and title!</h4>";
	exit();
}

$token = createToken(16); //16 bytes = 32 hex chars
$params = "";
if (isset($_POST["parameters"])) {
	$params = $_POST["parameters"];
}
global $TABLE_TOKENS, $database;
$tempQuery = "INSERT INTO $TABLE_TOKENS (Title, Token, QueryID, Parameters) VALUES ('" . $_POST["title"] . "', '$token', " . $_POST["query"] . ", '$params')";

$result = $database->query($tempQuery);
if ($result) {
	echo htmlspecialchars("https://". $_SERVER["HTTP_HOST"]) . "/admin/view.php?token=$token";
} else {
	echo "<h4>Error: " . $database->error . "</h4>";
}
