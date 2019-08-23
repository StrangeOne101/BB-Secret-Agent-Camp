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


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Cobs and Robbers Camp</title>
    <!-- <link rel="stylesheet" href="css/flier.css"> -->



    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.6/dist/jquery.fancybox.min.css" type="text/css" media="screen" />

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="css/home-gallery.css">
    <link href="css/gallery.css" rel="stylesheet">
    <link rel="stylesheet" href="css/mobile.css">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="A Boys Brigade and ICONZ camp for boys aged 8 to 10 in Canterbury! The theme this year is Cops and Robbers! Hosted at Waipara Adveture Center.">
    <meta name="author" content="Toby Strange (StrangeOne101)">

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.6/dist/jquery.fancybox.min.js"></script>
    <script src="js/gallery.js"></script>
</head>
<body>
    <div id="header">
        <img id="title" src="img/title.png" alt="Cops and Robbers Camp"/>
        <h2 style="margin-bottom: 50px">Thanks for coming!</h2>
    </div>

    <div id="carouselFeaturedPhotos" class="carousel slide" data-ride="carousel">
        <ol class="carousel-indicators">
            <li data-target="#carouselFeaturedPhotos" data-slide-to="0" class="active"></li>
            <li data-target="#carouselFeaturedPhotos" data-slide-to="1"></li>
            <li data-target="#carouselFeaturedPhotos" data-slide-to="2"></li>
        </ol>
        <div class="carousel-inner">
            <div class="carousel-item active" data-interval="8000">
                <img src="https://i.imgur.com/fdSJKLY.jpg" class="d-block w-100 featuredImage" alt="...">
                <div class="carousel-caption d-none d-md-block">
                    <p></p>
                </div>
            </div>
            <div class="carousel-item" data-interval="8000">
                <img src="https://i.imgur.com/bPnWl8T.jpg" class="d-block w-100 featuredImage" alt="...">
                <div class="carousel-caption d-none d-md-block">
                    <p></p>
                </div>
            </div>
            <div class="carousel-item" data-interval="8000">
                <img src="https://i.imgur.com/gLUdY3e.jpg" class="d-block w-100 featuredImage" alt="...">
                <div class="carousel-caption d-none d-md-block">
                    <p></p>
                </div>
            </div>
        </div>
        <a class="carousel-control-prev" href="#carouselFeaturedPhotos" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#carouselFeaturedPhotos" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>

    <div id="gallery-parent" style="margin-top: 40px">
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
                //if($index % $images_per_row == 0) { echo '<div class="clear"></div>'; }
            }
            echo '<div class="clear"></div>';
        }
        else {
            echo '<p>There are no images in this gallery.</p>';
        }
        ?>

    </div>

    <div id="footer" class="bottom-container">
        <div class="bottom">

            <h4>Thanks to everyone that made this camp happen! We couldn't have done it without you!</h4>
            <h4>Special thanks to <a href="https://www.facebook.com/MarcJensenPhotographer/">Marc Jensen</a> for these amazing photos!</h4>
            <div style="height: 20px"></div>
            <div id="logos">
                <a href="http://site.bb.org.nz/"><img src="img/logo-bb.png" alt="Boys Brigade Logo"/></a>
                <a href="http://www.briscoes.co.nz/"><img src="img/logo-morgan-and-morgan2.png" alt="Morgan and Morgan Logo"/></a>
                <a href=""><img src="img/logo-police-dp.png" alt="Police Department Logo"/></a>
                <a href="https://www.blacklightgroup.co.nz/"><img src="img/logo-blacklight2.png" alt="Blacklight Logo"/></a>
                <a href="http://site.iconz.org.nz/"><img src="img/logo-iconz.png" alt="ICONZ Logo"/></a>

            </div>
        </div>

    </div>

</body>

</html>