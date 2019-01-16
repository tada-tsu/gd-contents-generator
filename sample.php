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
    'sample_contents_image.png'
);

$generator->drawDetails("sample text", "sample text", "10")
    ->generateContentsImage()
    ->save('sample.png');
