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
$headers .= 'From: Space Camp<info@spacecamp.co.nz>' . PHP_EOL;

function email($to, $subject, $message) {
    global $headers;
    mail($to, $subject, $message, $headers);
}

function emailNoHTML($to, $subject, $message) {
	global $headers;
	mail($to, $subject, $message, "From: Space Camp<info@spacecamp.co.nz>");
}

/**
 * @param string $filename The filename of the email to read
 * @param array $variables An array of variables to parse in the document
 * @param boolean $html If this email should be an HTML email
 * @return string The data from the file
 */
function getEmailFile($filename, $variables = array(), $html = true) {
    $myfile = fopen("./emails/" . $filename, "r"); //Read the email file we are going to send
    if ($myfile == null) {
        echo "Something went really wrong! Could not locate file \"./emails/" . $filename . "\""; //o shit son
        return "";
    }
    $message = fread($myfile, filesize("./emails/" . $filename));
    if ($html == true) {
    	$message = str_replace("\n", "<br>", $message);
	}
    if (strpos($message, "<html>") == false && strpos($message, "<body>") == false && $html) {
        $message = "<html><body>$message</body></html>";
    }
    foreach($variables as $key => $value) {
        $message = str_replace('$' . $key, htmlspecialchars($value), $message);
    }
    return $message;
}

/**
 * Emails myself as well as the sending email about an error that has occured
 * @param $data
 * @param $error
 */
function sendErrorEmail($data, $error) {
	$timezone = 12; //GMT + 12

	$string = "A registration failed at " . date("d/m/y h:iA", strtotime("+$timezone hours")) . ". \r\n\r\nError message: $error \r\n\r\nData dump: \r\n\r\n$data";
	$email = "info@spacecamp.co.nz";

	$headers = "From: Space Camp <info@spacecamp.co.nz>\r\nCc: strange.toby@gmail.com";

	$string = wordwrap($string, 70, "\r\n");

	mail($email, "Site Problems", $string, $headers);
}

?>