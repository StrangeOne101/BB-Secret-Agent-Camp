<?php
/**
 * Created by PhpStorm.
 * Author: StrangeOne101 (Toby Strange)
 * Date: 06-Mar-19
 */

if (!(isset($open) && $open)) {
	header("HTTP/1.1 403 Forbidden"); //Prevent it from being seen in a browser
	exit;
}

include_once("debug.php");

/**
 * Finds the location of the config file for the database. The relative path can change
 * depending on the source of the original script, which is why this looks it up.
 * @return null|string The path. Will be null if not found.
 */
function findConfig() {
	$ini_array = null;
	$inipath = "config/stripe.ini";
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

$stripe_ready = false;

function is_stripe_ready() {
	global $stripe_ready;
	return $stripe_ready;
}

//Declare the variables. Do not insert the real
//keys here. Insert them into the stripe config.
$STRIPE_SECRET_KEY = $STRIPE_PUBLIC_KEY = "";

global $STRIPE_SECRET_KEY, $STRIPE_PUBLIC_KEY; //Globalize them

try {
	$ini_array = parse_ini_file(findConfig());

	$STRIPE_SECRET_KEY = $ini_array["SecretKey"] or "sk_test_notarealkey";
	$STRIPE_PUBLIC_KEY = $ini_array["PublicKey"] or "pk_test_notarealkey";
} catch (Exception $ex) {
	debug("Failed to get stripe details: " + $ex->getMessage());
	return;
}

try {
	\Stripe\Stripe::setApiKey("sk_test_ivMRVNqpRWvmGV99G4hqyWQ2");

	$stripe_ready = true;
} catch (Exception $ex) {
	debug("Failed to set stripe key: " + $ex->getMessage());
	return;
}