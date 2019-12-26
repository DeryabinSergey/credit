<?php

class PictureUtils extends FilesUtils
{
    /**
     * Типы размеров привью
     */
    const PREVIEW_IMAGE_TYPE_SMALL = 1;
    const PREVIEW_IMAGE_TYPE_MEDIUM = 2;
    const PREVIEW_IMAGE_TYPE_BIG = 3;

    protected static $filePostfixList = array(
            self::PREVIEW_IMAGE_TYPE_SMALL => null,
            self::PREVIEW_IMAGE_TYPE_MEDIUM => '-m',
            self::PREVIEW_IMAGE_TYPE_BIG => '-b'
    );

    /**
     * Вернет список разрешенных типов файлов
     * @return Array of ImageType
     */
    public static function getImageTypeList()
    {
        return array(
            new ImageType(ImageType::GIF),
            new ImageType(ImageType::JPEG),
            new ImageType(ImageType::PJPEG),
            new ImageType(ImageType::PNG),
            new ImageType(ImageType::BMP)
        );
    }

    /**
     * Вернет список допустимых mime/type для изображений
     * @staticvar Array $list
     * @return Array
     */
    public static function getImageMimeTypeList()
    {
        static $list = array();

        if (count($list))
            return $list;

        foreach (self::getImageTypeList() as $item)
            $list[] = $item->getMimeType();

        return $list;
    }

    /**
     * Получить список файлов привью для объекта
     * @param Identifiable $object
     * @return array
     */
    public static function getPreviewFiles(Identifiable $object)
    {
        $files = array();

        foreach (self::getAvailablePreviewTypes($object) as $type)
            $files[$type] = $object->getPreviewPath($type);

        return $files;
    }

    /**
     * Получить список доступных типов привью для объекта
     * @param Identifiable $object
     * @return array
     */
    public static function getAvailablePreviewTypes(Identifiable $object)
    {
        $types = array();

        if ($object instanceof PreviewBigPicture || $object instanceof PreviewDifferentBigPicture)       $types[] = self::PREVIEW_IMAGE_TYPE_BIG;
        if ($object instanceof PreviewMediumPicture || $object instanceof PreviewDifferentMediumPicture)    $types[] = self::PREVIEW_IMAGE_TYPE_MEDIUM;
        if ($object instanceof PreviewPicture || $object instanceof PreviewDifferentPicture)          $types[] = self::PREVIEW_IMAGE_TYPE_SMALL;

        return $types;
    }

    public static function getPreviewResizeSizes(Identifiable $object, $type)
    {
        if ($object instanceof Landing) {
            if ($type == self::PREVIEW_IMAGE_TYPE_SMALL) {
                return array(Constants::LANDING_PREVIEW_SMALL_WIDTH, Constants::LANDING_PREVIEW_SMALL_HEIGHT);
            } else {
                Assert::isUnreachable("Should define sizes first");
            }
        } else {
            Assert::isUnreachable("Should define object first");
        }
    }

    /**
     * постфикс в названии файла привью
     *
     * @param int $type
     * @return string
     */
    public static function getPreviewFilePostfixByType($type = self::PREVIEW_IMAGE_TYPE_SMALL)
    {
        Assert::isIndexExists(self::$filePostfixList, $type);

        return self::$filePostfixList[$type];
    }

    public static function getPreviewPathByObject($object)
    {
        if ($object instanceof Landing) {
           return UPLOAD_PATH . PREVIEW_PATH_LANDING . self::getNestingPathById($object->getId());
        } else {
            Assert::isUnreachable('Define data in PictureUtils first');
        }
    }

    public static function getPreviewUrlByObject($object)
    {
        if ($object instanceof Landing) {
           return UPLOAD_URL . PREVIEW_PATH_LANDING . self::getNestingPathById($object->getId());
        } else {
            Assert::isUnreachable('Define data in PictureUtils first');
        }
    }
    
    public static function getImageResizeSizes(Identifiable $object, $thumb = false) 
    {
        if ($object instanceof HotelImage) {
            return $thumb ? array(Constants::HOTEL_IMAGE_PREVIEW_SIZE, Constants::HOTEL_IMAGE_PREVIEW_SIZE) : array(Constants::HOTEL_IMAGE_SIZE, Constants::HOTEL_IMAGE_SIZE);
        } else {
            Assert::isUnreachable('Define data in PictureUtils first');
        }
    }

    public static function getImagePathByObject($object)
    {
        $nestingPath = self::getNestingPathById($object instanceof ImageUniqueFileName ? $object->getFileName() : $object->getId());
        $path = "";
        
        if ($object instanceof HotelImage) {
            $path = UPLOAD_PATH . IMAGE_PATH_HOTEL_IMAGE;
        } else {
            Assert::isUnreachable('Define data in PictureUtils first');
        }
        
        return $path . $nestingPath;
    }

    public static function getImageUrlByObject($object)
    {
        $nestingPath = self::getNestingPathById($object instanceof ImageUniqueFileName ? $object->getFileName() : $object->getId());
        $path = "";
        
        if ($object instanceof HotelImage) {
            $path = UPLOAD_URL . IMAGE_PATH_HOTEL_IMAGE;
        } else {
            Assert::isUnreachable('Define data in PictureUtils first');
        }
        
        return $path . $nestingPath;
    }

    public static function resize($originalPath, $size, $destPath = null, $crop = false, $resizeByBiggerSize = true, &$newSizes = array())
    {
        if (is_array($size) && count($size) == 2) {
            $width = $size[0];
            $height = $size[1];
        } else {
            $width = $height = $size;
        }
        $chmod = true;
        if (is_null($destPath)) {
            $destPath = $originalPath;
            $chmod = false;
        }

        $image = new Imagick();
        $image->readImage($originalPath);
        
        if ($crop) {
            $image->cropThumbnailImage($width, $height);
        } elseif ($image->getImageWidth() > $width || $image->getImageHeight() > $height) {
            
            /**
             * Здесь получилась интересная херня. При ресайзе широкой временной картинки по большей стороне картинка
             * уменьшается и иногда получается что при создании из временной нормальной картинки - меньшая сторона уже
             * не дотягивает до необходимого размера.
             * Для борьбы с этим добавил параметр - ресайз по меньшей стороне.
             * Т. е. в контроллере берется самый большой размер и картинка ресайзится до него, но по меньшей стороне.
             */
            if ($resizeByBiggerSize) {
                if (($image->getImageWidth() / $width) / ($image->getImageHeight() / $height) <= 1) {
                    $image->thumbnailImage(0, $height, false);
                } else {
                    $image->thumbnailImage($width, 0, false);
                }
            } else {
                if (($image->getImageWidth() / $width) / ($image->getImageHeight() / $height) <= 1) {
                    $image->thumbnailImage($width, 0, false);
                } else {
                    $image->thumbnailImage(0, $height, false);
                }
            }
        } 
        
        $image->setImageCompressionQuality(100);
        
        $image->writeImage($destPath);
        if ($chmod) { chmod($destPath, 0660); }
        
        $newSizes[0] = $image->getImageWidth();
        $newSizes[1] = $image->getImageHeight();
        
        $image->clear();
        $image->destroy();
    }

    public static function rotate($originalPath, $clockwise, $destPath = null, &$newSizes = array())
    {
        $degrees = $clockwise ? 90 : -90;
        return self::rotateCustom($originalPath, $degrees, $destPath, $newSizes);
    }

    public static function rotateCustom($originalPath, $degrees, $destPath = null, &$newSizes = array())
    {
        $chmod = true;
        if (is_null($destPath)) {
            $destPath = $originalPath;
            $chmod = false;
        }
        
        $image = new Imagick();
        $image->readImage($originalPath);
        $image->rotateImage(new ImagickPixel(), $degrees);
        $image->setImageCompressionQuality(100);
        $image->writeImage($destPath);
        if ($chmod) { chmod($destPath, 0660); }
        
        $newSizes[0] = $image->getImageWidth();
        $newSizes[1] = $image->getImageHeight();
        
        $image->clear();
        $image->destroy();
    }

    public static function crop($originalPath, $width, $height, $x, $y, $destPath = null)
    {
        $chmod = true;
        if (is_null($destPath)) {
            $destPath = $originalPath;
            $chmod = false;
        }
        
        $image = new Imagick();
        $image->readImage($originalPath);
        $image->cropImage($width, $height, $x, $y);
        $image->setImageCompressionQuality(100);
        $image->writeImage($destPath);
        if ($chmod) { chmod($destPath, 0660); }
        $image->clear();
        $image->destroy();
    }

    public static function borderImage($originalPath, $borderColor, $borderWidth, $borderHeight, $destPath = null)
    {
        $chmod = true;
        if (is_null($destPath)) {
            $destPath = $originalPath;
            $chmod = false;
        }

        $color=new ImagickPixel();
        $color->setColor($borderColor);
        $image = new Imagick();
        
        $image->readImage($originalPath);
        $image->borderImage($color,$borderWidth,$borderHeight);
        $image->writeImage($destPath);
        if ($chmod) { chmod($destPath, 0660); }
        
        $image->clear();
        $image->destroy();
    }

    /**
     * @param string $url
     * @param string $path
     * @param array $allowedTypes = array(new ImageType(), ...)
     * @return bool
     */
    public static function importImage($url, $path, $allowedTypes = array(), $readLength = 2048)
    {
        try {
            if (preg_match(PrimitiveString::URL_PATTERN, $url)
                && ($result = getimagesize($url))
                && (count($allowedTypes) && isset($result[2]) && in_array($result[2], ArrayUtils::getIdsArray($allowedTypes)))
                && self::checkDir($path)
            ) {
                try {
                    $readStream = FileInputStream::create($url);
                    $writeStream = FileOutputStream::create($path);

                    while(!$readStream->isEof())
                        $writeStream->write($readStream->read($readLength));

                    $readStream->close();
                    $writeStream->close();
                    chmod($path, 0660);

                    return true;

                } catch (IOException $e) {
                    if (file_exists($path)) unlink($path);
                }
            }
        } catch (Exception $e) {/* not_image*/}

        return false;
    }

    public static function watermarkImage($path, $text, $fontSize = 13)
    {
        /* Read the image in. This image will be watermarked. */
        $image = new Imagick($path);
        //$image->setImageFormat( "png" );

        /* This object will hold the font properties */
        $draw = new ImagickDraw();

        /* Setting gravity to the center changes the origo where annotation coordinates are relative to */
        $draw->setGravity(Imagick::GRAVITY_CENTER);

        /* Use a custom truetype font */
        //$draw->setFont(FONT_PATH."tahomabd.ttf" );
        $draw->setFont(FONT_PATH."micross.ttf" );

        /* Set the font size */
        $draw->setFontSize($fontSize);

        /* Create a new imagick object */
        $im = new imagick();

        /* Get the text properties */
        $properties = $im->queryFontMetrics( $draw, $text );

        /* Region size for the watermark. Add some extra space on the sides  */
        $watermark['w'] = intval( $properties["textWidth"] + 25 );
        $watermark['h'] = intval( $properties["textHeight"] + 25 );
        
        $height = $image->getImageHeight();
        $width = $image->getImageWidth();
        
        $offsetX = intval(($width - $properties["textWidth"]) / 2);
        $offsetY = intval(($height - $properties["textHeight"]) / 2);

        /* Create a canvas using the font properties. Add some extra space on width and height */
        $im->newImage( $watermark['w'], $watermark['h'], new ImagickPixel( "transparent" ) );

        /* Get a region pixel iterator to get the pixels in the watermark area */
        $it = $image->getPixelRegionIterator( $offsetX - 12, $offsetY - 12, $watermark['w'], $watermark['h'] );

        $luminosity = 0;
        $i = 0;

        /* Loop trough rows */
        while( $row = $it->getNextIteratorRow() ) {
            /* Loop trough each column on the row */
            foreach ( $row as $pixel ) {
                /* Get HSL values */
                $hsl = $pixel->getHSL();
                $luminosity += $hsl['luminosity'];
                $i++;
            }
        }

        /* If we are closer to white, then use black font and the other way around */
        $textColor = ( ( $luminosity / $i )> 0.5 ) ? new ImagickPixel( "#00000060" ) : new ImagickPixel( "#ffffff60" );

        /* Use the same color for the shadow */
        $draw->setFillColor( $textColor );

        /* Use png format */
        //$im->setImageFormat( "png" );

        /* Annotate some text on the image */
        $im->annotateImage( $draw, 0, 0, 0, $text );
        //$im->setImageOpacity(0.7);

        /* Clone the canvas to create the shadow */
        $watermark = $im->clone();

        /* Set the image bg color to black. (The color of the shadow) */
        $watermark->setImageBackgroundColor( $textColor );
        /* Create the shadow (You can tweak the parameters to produce "different" kind of shadows */
        $watermark->shadowImage( 80, 2, 2, 2 );

        /* Composite the text on the background */
        $watermark->compositeImage( $im, Imagick::COMPOSITE_OVER, 0, 0 );
        

        /* Composite the watermark on the image to the top left corner */
        $image->compositeImage( $watermark, Imagick::COMPOSITE_OVER, $offsetX - 12, $offsetY - 12 );

        $image->writeImage($path);
        $image->destroy();
        $im->destroy();
        $watermark->destroy();
    }
}