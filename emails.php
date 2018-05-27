<?php
if (!(isset($open) && $open)) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}
function sendParentEmail($email, $name, $refno) {
    $string = "Thanks for registering $name for Secret Agent Camp.\n\nPlease ensure the $60 camp fee is paid before 11 " .
    "August 2017 to Confirm registration.\n\nDue: $60.00\nAccount Number: 03 0855 0359326 01 \nReference: $refno" .
    "\nCode: 221\n\nLooking forward to seeing $name at camp, its going to be a fantastic weekend.\nIf you have any " .
    "questions please contact your Boys' Brigade / ICONZ leader or the Camp team at info@secretagentcamp.co.nz\n\nKind " .
    "Regards,\nThe Secret Agent Camp Team";
    
    $newstring = "";
    
    foreach (explode("\n", $string) as $s) {
        wordwrap($s, 70, "\n");
        
        $newstring .= $s . "\n";
    }
    
    $headers = "From: info@secretagentcamp.co.nz\r\nBcc: info@secretagentcamp.co.nz";
    $subject = "Secret Agent Camp Registration";
    
    mail($email, $subject, $newstring, $headers);
}

function sendLeaderEmail($email, $name, $refno) {
    $string = "Thanks $name for registering as a leader for Secret Agent Camp.\n\nPlease ensure the $45 leaders camp fee" .
    "is paid before 11 August 2017 to Confirm registration.\n\nDue: $45.00\nAccount Number: 03 0855 0359326 01 \nReference:" .
    "$refno\nCode: 221\n\nLooking forward to seeing you at camp, its going to be a fantastic weekend.\nIf you have " .
    "any questions please contact the Camp team at info@secretagentcamp.co.nz or David Blackler on 021 182 4677.\n\nKind Regards," .
    "\nThe Secret Agent Camp Team";
    
    $newstring = "";
    
    foreach (explode("\n", $string) as $s) {
        wordwrap($s, 70, "\n");
        
        $newstring .= $s . "\n";
    }
    
    $headers = "From: info@secretagentcamp.co.nz\r\nBcc: info@secretagentcamp.co.nz";
    $subject = "Secret Agent Camp Leader Registration";
    
    mail($email, $subject, $newstring, $headers);
}

function sendErrorEmail($data, $error) {
    
    $timezone = 12; //GMT + 12
    
    $string = "A registration failed at " . date("d/m/y h:iA", strtotime("+$timezone hours")) . ". \r\n\r\nError message: $error \r\n\r\nData dump: \r\n\r\n$data";
    $email = "info@secretagentcamp.co.nz";
    
    $headers = "From: Secret Agent Camp <info@secretagentcamp.co.nz>\r\nCc: strange.toby@gmail.com";
    
    $string = wordwrap($string, 70, "\r\n");
    
    mail($email, "Site Problems", $string, $headers);
}
?>
