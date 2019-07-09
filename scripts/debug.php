<?php
if (!(isset($open) && $open)) {
    header("HTTP/1.1 403 Forbidden"); //Prevent it from being seen in a browser
    exit;
}


$debugVal = false;
global $debugVal;

$totalErrorsOnPage = array();

/**
 * Print some debug. Use <code>debug()</code> in the console of
 * a browser to see it. However this only works if 
 * debug is enabled.
 * @param string $data The data to print
 */

function debug($data) {
    global $debugVal;
    global $totalErrorsOnPage;
    if ($debugVal) {
        echo("<div class='hidden debug'>$data</div>");
    }
    array_push($totalErrorsOnPage, $data);
}


/**
 * Gets the total amount of errors that have occured within all
 * PHP scripts. 
 */

function getErrors() {
    global $totalErrorsOnPage;
    $totalErrors = "";

    foreach ($totalErrorsOnPage as $error) { //Go through each error and make it into one string on multiple lines
        $totalErrors = $totalErrors . "\n" . trim($error);
    }
    if (strlen($totalErrors) > 0) { //Remove the extra \n on the front
        return trim(substr($totalErrors, 1));
    }
    return "";
}