# GD Contents Generator

## install

```
composer require tada-tsu/gd-contents-generator
```


## usage

```
# Create color object

$color1 = new GDContentsGenerator\Color(0, 0, 0, true); // black background, white text

$color2 = new GDContentsGenerator\Color(255, 255, 255); // white background, black text



# Create generator instance

$generator1 = new GDContentsGenerator\Generator(
    $color1,
    'path/to/image'
);

$generator2 = new GDContentsGenerator\Generator(
    $color2,
    'path/to/image'
);



# Save or return result

$generator1->drawDetails("top text", "bottom text", "left text")
    ->generateContentsImage()
    ->save("sample.png"); // save image

$generator2>drawDetails("top text", "bottom text", "left text")
    ->generateContentsImage();
    
$result = $generator2->generate(); // return result image resource

$result = $generator2->generate('jpg'); // return result image binary
$result = $generator2->generate('png'); // return result image binary
$result = $generator2->generate('gif'); // return result image binary
$result = $generator2->generate('webp'); // return result image binary

```

## sample

```
php sample.php
open sample.png
```
