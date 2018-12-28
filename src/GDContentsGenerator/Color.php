<?php

namespace GDContentsGenerator;

/**
 * Contents colors value object.
 * used on GDContentsGenerator
 */
class Color
{
    private static $value;
    private static $colors;
    private static $whiteText;

    /**
     * Set color
     *
     * @param int $r
     * @param int $g
     * @param int $b
     * @param bool $whiteText
     */
    public function __construct(int $r, int $g, int $b, bool $whiteText = null)
    {
        self::$colors = [
            $r,
            $g,
            $b,
        ];
        self::$whiteText = $whiteText == true;
    }

    /**
     * Return colors array
     *
     * @return array
     */
    public function getColors()
    {
        return self::$colors;
    }

    /**
     * Return text colors array
     *
     * @return array
     */
    public function getTextColor()
    {
        if ($this->isWhiteText()) {
            return [255, 255, 255];
        } else {
            return [16, 16, 16];
        }
    }

    /**
     * Return white text
     *
     * @return boolean
     */
    public function isWhiteText()
    {
        return self::$whiteText;
    }
}
