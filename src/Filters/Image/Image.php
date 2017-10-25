<?php

namespace Kibo\Phast\Filters\Image;

interface Image {

    const TYPE_JPEG = 'jpg';

    const TYPE_PNG  = 'png';

    /**
     * @return integer
     */
    public function getWidth();

    /**
     * @return integer
     */
    public function getHeight();

    /**
     * @param integer $width
     * @return void
     */
    public function setWidth($width);

    /**
     * @param integer $height
     * @return void
     */
    public function setHeight($height);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param integer $compression
     * @return null
     */
    public function setCompression($compression);

}
