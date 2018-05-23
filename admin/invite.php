<?php
/**
 * Created by PhpStorm.
 * Author: StrangeOne101 (Toby Strange)
 */

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET["token"])) {
        $token = $_GET["token"];
    }
}