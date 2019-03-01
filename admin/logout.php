<?php
if(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] != "on" && $_SERVER["HTTP_HOST"] != "localhost")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}

$open = true;
global $open;

include_once("../scripts/debug.php");

session_start();

if ($_SESSION["email"]) {
    session_destroy();

    session_start();
    $_SESSION["loggedout"] = true;
    
    header("Location: https://" . $_SERVER["HTTP_HOST"] . "/admin/login.php");
}