<?php

namespace GDContentsGenerator;

use GDContentsGenerator\Color;

class Generator
{
    protected $imageLoadFunctionsMap = [
        'jpg'  => "imagecreatefromjpeg",
        'jpeg' => "imagecreatefromjpeg",

        'png'  => "imagecreatefrompng",

        'gif'  => "imagecreatefromgif",

        'bmp'  => "imagecreatefrombmp",

        'webp' => "imagecreatefromwebp",
    ];

    /**
     *
     *
     * @var Color $color
     */
    protected $color;

    /**
     *
     *
     * @var int $padding
     */
    protected $padding;

    /**
     *
     *
     * @var int $sampledPadding
     */
    protected $sampledPadding;

    /**
     * path/to/font.ttf
     * default use "Roboto"
     *
     * @var string $fontPath
     */
    protected $fontPath;

    /**
     * path/to/tipImagePath.png
     * default use "star"
     *
     * @var string $tipImagePath
     */
    protected $tipImagePath;

    /**
     * for base image resource
     *
     * @var resource $baseImage
     */
    private $baseImage;

    /**
     * Generate image size
     *
     * @var int
     */
    private $imageSize;

    /**
     * sampling rate
     *
     * @var int
     */
    private $sampling;

    /**
     * image size timed sampling rate
     *
     * @var int
     */
    private $sampledImageSize;

    /**
     * generate base image
     *
     * @param Color $color
     * @param string $imagePath
     * @param integer $imageSize
     * @param integer $sampling
     */
    public function __construct(Color $color, string $imagePath, int $imageSize = 400, int $sampling = 3)
    {
        $this->color            = $color;
        $this->imagePath        = $imagePath;
        $this->imageSize        = $imageSize;
        $this->sampling         = $sampling;
        $this->sampledImageSize = $imageSize * $sampling;

        $this->padding        = $imageSize / 5;
        $this->sampledPadding = $this->padding * $sampling;
        $this->fontPath       = dirname(__DIR__) . '/fonts/Roboto-Medium.ttf';
        $this->tipImagePath   = dirname(__DIR__) . '/images/star.png';

        $this->baseImage = $this->createBaseImage();
    }

    /**
     * draw text and image
     *
     * @param string $topText
     * @param string $bottomText
     * @param string $leftText
     * @return Generator $this
     */
    public function drawDetails(string $topText, string $bottomText, string $leftText)
    {
        $this->drawTopText($this->baseImage, $topText);
        $this->drawBottomText($this->baseImage, $bottomText);

        $this->drawLeftText($this->baseImage, $leftText);
        $this->drawRightImage($this->baseImage, $this->tipImagePath);

        return $this;
    }

    /**
     * generate contents image
     *
     * @return Generator $this
     */
    public function generateContentsImage()
    {
        $contentsImage = $this->loadImage($this->imagePath);

        $ratio            = imagesx($contentsImage) / imagesy($contentsImage);
        $dependsRatioArgs = [
            $ratio > 1 ? $this->sampledPadding : ($this->sampledImageSize - ($this->sampledImageSize - $this->sampledPadding * 2) * $ratio) / 2,
            $ratio > 1 ? ($this->sampledImageSize - ($this->sampledImageSize - $this->sampledPadding * 2) / $ratio) / 2 : $this->sampledPadding,
            0,
            0,
            $ratio > 1 ? $this->sampledImageSize - $this->sampledPadding * 2 : ($this->sampledImageSize - $this->sampledPadding * 2) * $ratio,
            $ratio > 1 ? ($this->sampledImageSize - $this->sampledPadding * 2) / $ratio : $this->sampledImageSize - $this->sampledPadding * 2,
            imagesx($contentsImage),
            imagesy($contentsImage),
        ];

        imagecopyresized(
            $this->baseImage,
            $contentsImage,
            ...$dependsRatioArgs
        );

        return $this;
    }

    /**
     * save image
     *
     * @param string $savePath
     * @return bool
     */
    public function save(string $savePath)
    {
        return imagepng($this->antialiasImage($this->baseImage), $savePath);
    }

    /**
     * get antialiased image
     *
     * @return Generator $this
     */
    public function generate()
    {
        return $this->antialiasImage($this->baseImage);
    }

    /**
     * auto ditect ext and load image
     *
     * @param string $path
     * @return Resource 
     */
    protected function loadImage(string $path)
    {
        $ext = preg_replace('/.*\.(.*)$/', '$1', basename($path));

        return $this->imageLoadFunctionsMap[$ext]($path);
    }

    protected function createBaseImage()
    {
        $image = imagecreatetruecolor($this->sampledImageSize, $this->sampledImageSize);

        imagesavealpha($image, true);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 127, 127, 127, 127));

        $color = $this->color->getColors();

        $ellipseColor = imagecolorallocate($image, ...$color);
        imagefilledellipse($image, $this->sampledImageSize / 2, $this->sampledImageSize
            / 2, $this->sampledImageSize, $this->sampledImageSize, $ellipseColor);

        return $image;
    }

    /**
     * draw text to top
     *
     * @param Resource $imageResource
     * @param string $text
     * @return void
     */
    protected function drawTopText($imageResource, string $text)
    {
        $this->drawingTextToCenter(
            $imageResource,
            $text,
            $this->imageSize * 0.15,
            $this->sampledImageSize / 2,
            $this->imageSize * 0.225,
            $this->fontPath,
            $this->color->getTextColor()
        );
    }

    protected function drawBottomText($imageResource, string $text)
    {
        $this->drawingTextToCenter(
            $imageResource,
            $text,
            $this->imageSize * 0.15,
            $this->sampledImageSize / 2,
            $this->sampledImageSize - $this->imageSize * 0.225,
            $this->fontPath,
            $this->color->getTextColor()
        );
    }

    protected function drawLeftText($imageResource, string $text)
    {
        $this->drawingTextToCenter(
            $imageResource,
            $text,
            $this->imageSize * 0.2,
            $this->imageSize * 0.275,
            $this->sampledImageSize / 2,
            $this->fontPath,
            $this->color->getTextColor()
        );
    }

    protected function drawRightImage($imageResource, string $tipImagePath)
    {
        $starImage = $this->loadImage($tipImagePath);
        imagecopyresized(
            $imageResource,
            $starImage,
            $this->sampledImageSize - $this->imageSize * 0.45,
            $this->sampledImageSize / 2 - $this->imageSize * 0.15,
            0,
            0,
            $this->imageSize * 0.3,
            $this->imageSize * 0.3,
            imagesx($starImage),
            imagesy($starImage)
        );
    }

    protected function drawingTextToCenter($imageResource, string $text, int $fontSize, int $x, int $y, string $fontPath, array $color)
    {
        $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
        $w    = $bbox[2] - $bbox[6];
        $h    = $bbox[3] - $bbox[7];

        $textColor = imagecolorallocate($imageResource, ...$color);
        imagettftext(
            $imageResource,
            $fontSize,
            0,
            $x - $w / 2,
            $y + $h / 2,
            $textColor,
            $fontPath,
            $text
        );
    }

    /**
     * get antialiased image
     *
     * @param Resource $imageResource
     * @return Resource
     */
    protected function antialiasImage($imageResource)
    {
        $antialiased = imagecreatetruecolor($this->imageSize, $this->imageSize);
        imagesavealpha($antialiased, true);
        imagefill($antialiased, 0, 0, imagecolorallocatealpha($antialiased, 127, 127, 127, 127));
        imagecopyresampled(
            $antialiased,
            $imageResource,
            0,
            0,
            0,
            0,
            $this->imageSize,
            $this->imageSize,
            imagesx($imageResource),
            imagesy($imageResource)
        );

        return $antialiased;
    }

}
