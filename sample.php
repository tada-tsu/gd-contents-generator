<?php

include "vendor/autoload.php";

$colorArray = [
    255,
    255,
    255,
];

$color     = new GDContentsGenerator\Color(...$colorArray);
$generator = new GDContentsGenerator\Generator(
    $color,
    'src/images/star.png'
);

$generator->drawDetails("aaa", "bbb", "10")
    ->drawcontentsImage()
    ->save("sample.png");
