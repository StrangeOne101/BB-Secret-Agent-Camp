<?php 
if (!(isset($open) && $open)) {
    header("HTTP/1.1 403 Forbidden"); //Prevent it from being seen in a browser
    exit;
}

$FOOT_NOTICE = "<i>This is an automated message sent to you by the Team Section 
Camp team. If you have any questions or concerns, please reply to this email and 
we can get back to you.</i>";

global $FOOT_NOTICE;

$headers  ='"MIME-Version: 1.0' . PHP_EOL;
$headers .= 'Content-Type: text/html; charset=ISO-8859-1' . PHP_EOL;
$headers .= 'From: Site<info@site.com>' . PHP_EOL;

function email($to, $subject, $message) {
    global $headers;
    mail($to, $subject, $message, $headers);
}

/**
 * @param string $filename The filename of the email to read
 * @param array $variables An array of variables to parse in the document
 * @return string The data from the file
 */
function getEmailFile($filename, $variables = array()) {
    $myfile = fopen("./emails/" . $filename, "r"); //Read the email file we are going to send
    if ($myfile == null) {
        echo "Something went really wrong!"; //o shit son
        return "";
    }
    $message = fread($myfile, filesize("./emails/" . $filename));
    $message = str_replace("\n", "<br>", $message);
    if (strpos($message, "<html>") == false && strpos($message, "<body>") == false) {
        $message = "<html><body>$message</body></html>";
    }
    foreach($variables as $key => $value) {
        $message = str_replace('$' . $key, htmlspecialchars($value), $message);
    }
    return $message;
}

?>