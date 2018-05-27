<?php
/**
 * Created by PhpStorm.
 * User: StrangeOne101 (Toby Strange)
 * Date: 26-May-18
 */

function createToken($length) {
    if (!isset($length)) {
        $length = 16;
    }

    return bin2hex(openssl_random_pseudo_bytes($length)); //Creates random bytes and converts it to hex
}