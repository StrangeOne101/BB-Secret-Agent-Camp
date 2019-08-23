<?php

if(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] != "on" && $_SERVER["HTTP_HOST"] != "localhost")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
}

$open = true;
global $open;

include_once($_SERVER['DOCUMENT_ROOT'] . '/scripts/debug.php');
include_once($_SERVER['DOCUMENT_ROOT'] . "/scripts/gallery_functions.php");


set_time_limit(60 * 10); //This task can take a while, so let it run a long time

$fname = $_SERVER['DOCUMENT_ROOT'] . "/gallery_data.txt";

unlink($fname); //Delete existing file

$myfile = fopen($fname, "w");

$image_files = get_files($images_dir);
if(count($image_files)) {
    $index = 0;
    foreach ($image_files as $index => $file) {
        $index++;
        $thumbnail_image = $thumbs_dir . $file;
        if (!file_exists($thumbnail_image)) { //If a thumbnail doesn't exist, create it before we echo it
            $extension = get_file_extension($thumbnail_image);
            if ($extension) {
                make_thumb($images_dir . $file, $thumbnail_image, $thumbs_width);
            }
        }
        fwrite($myfile,'<a href="' . $images_dir . $file . '" class="photo-link fancybox" data-fancybox="2019 Cops and Robbers Camp" data-caption="' . get_caption($file) . '" rel="gallery"><img src="' . $thumbnail_image . '" class="zoom img-fluid"/></a>');
        //if($index % $images_per_row == 0) { echo '<div class="clear"></div>'; }
    }
} else {
    fwrite($myfile, '<p>There are no images in this gallery.</p>');
}

fclose($myfile);

echo "Task done.";