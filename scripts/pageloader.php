<?php
if (!(isset($open) && $open)) {
    header("HTTP/1.1 403 Forbidden"); //Prevent it from being seen in a browser
    exit;
}

/***
 * Loads a file and reads the content of it.
 * Returns the content of the file, or nothing
 * if not found.
 */
if (!function_exists("loadPage")) {
    function loadPage($name) {
        $myfile = fopen($name, "r");
        if ($myfile == null) {
            debug("Unable to find page to load named $name");
            return "";
        }
        return fread($myfile,filesize($name));
        fclose($myfile);
    }
}

?>