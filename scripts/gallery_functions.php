<?php

if (!(isset($open) && $open)) {
    header("HTTP/1.1 403 Forbidden"); //Prevent it from being seen in a browser
    exit;
}

$images_dir = '/img/gallery/';
$thumbs_dir = '/img/gallery/thumbs/';
$captions_file_path = '/img/gallery/captions.txt';

$thumbs_width = 400;
$images_per_row = 3;

//Read captions
$image_captions = array();
$captions_file = fopen($_SERVER['DOCUMENT_ROOT'] . $captions_file_path, "r");

global $thumbs_width, $images_dir, $thumbs_dir;

if ($captions_file) {
    while (($line = fgets($captions_file)) !== false) { //Read each line
        $image_name = substr($line, 0, strrpos($line, ":"));
        $image_caption = trim(substr($line, strlen($image_name) + 1));

        $image_captions[$image_name] = trim($image_caption);
    }

    fclose($captions_file);
} else {
    // error opening the file.
}

//Make directories
if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $images_dir)) mkdir($_SERVER['DOCUMENT_ROOT'] . $images_dir);
if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $thumbs_dir)) mkdir($_SERVER['DOCUMENT_ROOT'] . $thumbs_dir);

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}

/* function:  generates thumbnail */
function make_thumb($src,$dest,$desired_max) {
    /* read the source image */
    $source_image = null;

    if (endsWith(strtolower($src), ".jpg") || endsWith(strtolower($src), ".jpeg")) $source_image = imagecreatefromjpeg($_SERVER['DOCUMENT_ROOT'] . $src);
    else if (endsWith(strtolower($src), ".png")) $source_image = imagecreatefrompng($_SERVER['DOCUMENT_ROOT'] . $src);
    else if (endsWith(strtolower($src), ".bmp")) $source_image = imagecreatefrombmp($_SERVER['DOCUMENT_ROOT'] . $src);
    else if (endsWith(strtolower($src), ".gif")) $source_image = imagecreatefromgif($_SERVER['DOCUMENT_ROOT'] . $src);
    else return;


    $width = imagesx($source_image);
    $height = imagesy($source_image);
    /* find the "desired height" of this thumbnail, relative to the desired width  */
    $desired_width = $desired_max;
    $desired_height = floor($height*($desired_max/$width));

    if ($height > $width) {
        $desired_height = $desired_max;
        $desired_width = floor($width*($desired_max/$height));
    }

    /* create a new, "virtual" image */
    $virtual_image = imagecreatetruecolor($desired_width,$desired_height);
    /* copy source image at a resized size */
    imagecopyresized($virtual_image,$source_image,0,0,0,0,$desired_width,$desired_height,$width,$height);
    /* create the physical thumbnail image to its destination */
    imagejpeg($virtual_image, $_SERVER['DOCUMENT_ROOT'] . $dest);

    unset($virtual_image);
    unset($source_image);
}

/* function: returns caption for image file */
function get_caption($file) {
    global $image_captions;
    $file = substr($file, 0, strrpos($file, ".")); //Cut off extension

    if (array_key_exists($file, $image_captions)) {
        return $image_captions[$file];
    }

    return "";
}

/* function:  returns files from dir */
function get_files($images_dir,$exts = array('jpg', 'jpeg', 'png', 'gif', 'bmp')) {
    $files = array();
    if($handle = opendir($_SERVER['DOCUMENT_ROOT'] . $images_dir)) {
        while(false !== ($file = readdir($handle))) {
            $extension = strtolower(get_file_extension($file));
            if($extension && in_array($extension,$exts)) {
                $files[] = $file;
            }
        }
        closedir($handle);
    }
    return $files;
}

/* function:  returns a file's extension */
function get_file_extension($file_name) {
    return substr(strrchr($file_name,'.'),1);
}
