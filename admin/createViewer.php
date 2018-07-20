<?php
/**
 * Created by PhpStorm.
 * Author: StrangeOne101 (Toby Strange)
 * Date: 19-Jul-18
 */
$open = true;
global $open;

include("../scripts/debug.php");
include("../scripts/database.php");
include("../scripts/tokens.php");

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

