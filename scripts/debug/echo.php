<?php
$path = $_SERVER['DOCUMENT_ROOT'];
//$path .= "/common/header.php";
//include_once($path);
echo $path . "<br>" . get_include_path() . PATH_SEPARATOR;
?>
