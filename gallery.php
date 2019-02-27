<?php
/**
 * Created by PhpStorm.
 * Author: StrangeOne101 (Toby Strange)
 * Date: 27-Feb-19
 */

if((!isset($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != "on") && $_SERVER["HTTP_HOST"] != "localhost")
{
	header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
	exit();
}
$open = true;
global $open;

include_once("scripts/debug.php");

$loggedIn = false;
$user = "";
$priv = 0;

session_start();

if(isset($_SESSION['username'])){
	$loggedIn = true;
	$user = $_SESSION['username'];
	//$priv = $_SESSION['permissions'];
} else {

}

//Functions and setup created from https://davidwalsh.name/generate-photo-gallery

//Settings
$images_dir = 'img/gallery/';
$thumbs_dir = 'img/gallery/thumbs/';
$captions_file_path = 'img/gallery/captions.txt';
$thumbs_width = 250;
$images_per_row = 3;

//Read captions
$image_captions = array();
$captions_file = fopen($captions_file_path, "r");
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
if (!file_exists($images_dir)) mkdir($images_dir);
if (!file_exists($thumbs_dir)) mkdir($thumbs_dir);

/* function:  generates thumbnail */
function make_thumb($src,$dest,$desired_width) {
	/* read the source image */
	$source_image = imagecreatefromjpeg($src);
	$width = imagesx($source_image);
	$height = imagesy($source_image);
	/* find the "desired height" of this thumbnail, relative to the desired width  */
	$desired_height = floor($height*($desired_width/$width));
	/* create a new, "virtual" image */
	$virtual_image = imagecreatetruecolor($desired_width,$desired_height);
	debug($virtual_image);
	debug("Break in between");
	/* copy source image at a resized size */
	imagecopyresized($virtual_image,$source_image,0,0,0,0,$desired_width,$desired_height,$width,$height);
	debug($virtual_image);
	/* create the physical thumbnail image to its destination */
	imagejpeg($virtual_image, $dest);
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
function get_files($images_dir,$exts = array('jpg', 'png', 'jpeg')) {
	$files = array();
	if($handle = opendir($images_dir)) {
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

?>

<!DOCTYPE html>
<html lang="en">
<head>

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="Register for Team Section Camp 2018 - Space Camp!">
	<meta name="author" content="Toby Strange (StrangeOne101)">
	<!--<meta name="robots" content="noindex">  <!-- This has been commented out as a trial to let google index the page -->
	<!--<meta name="googlebot" content="noindex"> -->

	<meta http-equiv="cache-control" content="no-cache, must-revalidate, post-check=0, pre-check=0" />
	<meta http-equiv="cache-control" content="max-age=0" />
	<meta http-equiv="expires" content="0" />
	<meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
	<meta http-equiv="pragma" content="no-cache" />

	<title>Cops and Robbers Camp - Gallery</title>

	<!-- Bootstrap Core CSS -->
	<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.6/dist/jquery.fancybox.min.css" type="text/css" media="screen" />

	<!-- Custom CSS -->
	<link href="css/gallery.css" rel="stylesheet">

	<link rel="shortcut icon" href="favicon.ico">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.6/dist/jquery.fancybox.min.js"></script>
	<script src="js/debug.js"></script>
    <script src="js/gallery.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
	<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->

</head>

<body>
Coming Soon

	<div id="gallery-parent">
		<?php //Generate the gallery
		$image_files = get_files($images_dir);
		if(count($image_files)) {
			$index = 0;
			foreach($image_files as $index=>$file) {
				$index++;
				$thumbnail_image = $thumbs_dir.$file;
				if(!file_exists($thumbnail_image)) { //If a thumbnail doesn't exist, create it before we echo it
					$extension = get_file_extension($thumbnail_image);
					if($extension) {
						make_thumb($images_dir.$file,$thumbnail_image,$thumbs_width);
					}
				}
				echo '<a href="',$images_dir.$file,'" class="photo-link fancybox" data-fancybox="2018 Space Camp" data-caption="', get_caption($file),'" rel="gallery"><img src="',$thumbnail_image,'" class="zoom img-fluid"/></a>';
				if($index % $images_per_row == 0) { echo '<div class="clear"></div>'; }
			}
			echo '<div class="clear"></div>';
		}
		else {
			echo '<p>There are no images in this gallery.</p>';
		}
		?>

	</div>
</body>
</html>