<?php

if(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] != "on" && $_SERVER["HTTP_HOST"] != "localhost")
{
    header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    exit();
} ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Cobs and Robbers Camp</title>
    <link rel="stylesheet" href="css/flier.css">
    <link rel="stylesheet" href="css/mobile.css">

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="A Boys Brigade and ICONZ camp for boys aged 8 to 10 in Canterbury! The theme this year is Cops and Robbers! Hosted at Waipara Adveture Center.">
    <meta name="author" content="Toby Strange (StrangeOne101)">
</head>
<body>

    <h3 id="header">BOYS' BRIGADE AND ICONZ PRESENTS</h3>
    <img id="title" src="img/title.png" alt="Cops and Robbers Camp"/>
    <h2 style="">Thanks for coming!</h2>
    <h3>Photos will be up in the next few days!</h3>

    <div id="footer" class="bottom-container">
        <div class="bottom">
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