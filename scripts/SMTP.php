<?php
/**
 * Created by PhpStorm.
 * Author: StrangeOne101 (Toby Strange)
 * Date: 07-Jul-18
 */
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_STRICT);

set_include_path("." . PATH_SEPARATOR . ($UserDir = dirname($_SERVER['DOCUMENT_ROOT'])) . "/pear/php" . PATH_SEPARATOR . get_include_path());
require_once "Mail.php";

$host = "ssl://mail.spacecamp.co.nz";
$username = "info@spacecamp.co.nz";
$password = "spacecamp1883";
$port = "465";
$to = "strange.toby@gmail.com";
$email_from = "info@spacecamp.co.nz";
$email_subject = "Subject Line Here: " ;
$email_body = "whatever you like" ;
$email_address = "info@spacecamp.co.nz";

$headers = array ('From' => $email_from, 'To' => $to, 'Subject' => $email_subject, 'Reply-To' => $email_address);
$smtp = Mail::factory('smtp', array ('host' => $host, 'port' => $port, 'auth' => true, 'username' => $username, 'password' => $password));
$mail = $smtp->send($to, $headers, $email_body);

echo "Test";
?>