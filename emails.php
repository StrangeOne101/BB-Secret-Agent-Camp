<?php
if (!(isset($open) && $open)) {
    header("HTTP/1.1 403 Forbidden");
    exit;
}
function sendParentEmail($email, $name, $refno) {
    $string = "This message shouldn't be blank.";
    
    $string = wordwrap($string, 70, "\r\n");
    
    $headers = "From: info@secretagentcamp.co.nz\r\nBcc: info@secretagentcamp.co.nz";
    $subject = "Secert Agent Camp Registration";
    
    mail($email, $subject, $string, $headers);
}

function sendLeaderEmail($email, $name, $refo) {
    $string = "Thanks for signing up for Secret Agent Camp! We appreciate you joining us.\n\nThe cost for leaders is just $10.\nIf you " .
    "could deposit the money in xxxxxxxxxxxxxxxxxxx, along with the reference number $refo, that'd be great.\n\nThanks,\nThe Secret Agent Camp Team";
    
    $string = wordwrap($string, 70, "\r\n");
    
    $headers = "From: info@secretagentcamp.co.nz\r\nBcc: info@secretagentcamp.co.nz";
    $subject = "Secert Agent Camp Leader Registration";
    
    mail($email, $subject, $string, $headers);
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