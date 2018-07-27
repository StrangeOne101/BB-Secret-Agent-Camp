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

function getBrowser()
{
	$u_agent = $_SERVER['HTTP_USER_AGENT'];
	$bname = 'Unknown';
	$platform = 'Unknown';
	$version= "";

	//First get the platform?
	if (preg_match('/linux/i', $u_agent)) {
		$platform = 'Linux';
	}
	elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
		$platform = 'Mac';
	}
	elseif (preg_match('/windows|win32/i', $u_agent)) {
		$platform = 'Windows';
	}

	// Next get the name of the useragent yes seperately and for good reason
	if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
	{
		$bname = 'Internet Explorer';
		$ub = "MSIE";
	}
	elseif(preg_match('/Firefox/i',$u_agent))
	{
		$bname = 'Mozilla Firefox';
		$ub = "Firefox";
	}
	elseif(preg_match('/Chrome/i',$u_agent))
	{
		$bname = 'Google Chrome';
		$ub = "Chrome";
	}
	elseif(preg_match('/Safari/i',$u_agent))
	{
		$bname = 'Apple Safari';
		$ub = "Safari";
	}
	elseif(preg_match('/Opera/i',$u_agent))
	{
		$bname = 'Opera';
		$ub = "Opera";
	}
	elseif(preg_match('/Netscape/i',$u_agent))
	{
		$bname = 'Netscape';
		$ub = "Netscape";
	}

	// finally get the correct version number
	$known = array('Version', $ub, 'other');
	$pattern = '#(?<browser>' . join('|', $known) .
		')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
	if (!preg_match_all($pattern, $u_agent, $matches)) {
		// we have no matching number just continue
	}

	// see how many we have
	$i = count($matches['browser']);
	if ($i != 1) {
		//we will have two since we are not using 'other' argument yet
		//see if version is before or after the name
		if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
			$version= $matches['version'][0];
		}
		else {
			$version= $matches['version'][1];
		}
	}
	else {
		$version= $matches['version'][0];
	}

	// check if we have a number
	if ($version==null || $version=="") {$version="?";}

	return array(
		'userAgent' => $u_agent,
		'name'      => $bname,
		'version'   => $version,
		'platform'  => $platform,
		'pattern'    => $pattern
	);
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
	/*$string .= "\r\n\r\nUser Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\r\n";

	$browser = get_browser(null, true);

	$string .= "Browser: " . $browser["parent"] . "\r\n";
	$string .= "Platform: " . $browser["platform"];*/

	$browser = getBrowser(); //Gets all the browser/device data in string format
	$string.= "User Agent: " . $browser["userAgent"] . "\r\n";
	$string.= "Browser: " . $browser["name"] . " " . $browser["version"] . "\r\n";
	$string.= "Platform: " . $browser["platform"];

	mail($email, "Site Problems", $string, $headers);
}

?>