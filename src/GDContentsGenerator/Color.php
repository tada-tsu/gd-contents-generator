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
     * @param int $value
     * @param int $r
     * @param int $g
     * @param int $b
     * @param bool $whiteText
     */
    public function __construct(int $value, int $r, int $g, int $b, bool $whiteText = null)
    {
        self::$value  = $value;
        self::$colors = [
            $r,
            $g,
            $b,
        ];
        self::$whiteText = $whiteText == true;
    }

    /**
     * Return stars ( means values divided by 100 )
     *
     * @return int
     */
    public function getStars(){
        return self::$value / 100;
    }
    
    /**
     * Return colors array
     *
     * @return array
     */
    public function getColors(){
        return self::$colors;
    }

    /**
     * Return white text
     *
     * @return boolean
     */
    public function isWhiteText(){
        return self::$whiteText;
    }
}
