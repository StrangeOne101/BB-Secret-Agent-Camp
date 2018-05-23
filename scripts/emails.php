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


?>