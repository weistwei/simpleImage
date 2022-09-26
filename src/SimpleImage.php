<?php

namespace NickStudio\Image;

use GdImage;
use Exception;

class SimpleImage{

    /** @var $sourceBase64String string */
    private $sourceBase64String;

    /** @var $sourceFilePath string */
    private $sourceFilePath;

    /** @var $imageResource resource|GdImage|false */
    private $imageResource;

    /** @var $width integer */
    private $width;

    /** @var $height integer */
    private $height;

    /** @var $imageType int */
    private $imageType;

    /** @var $bits integer */
    private $bits;

    /** @var $mime string */
    private $mime;

    /** @var $size float */
    private $size;

    /** @var $extension string */
    private $extension;

    /** @var $imageDataCache []false|string */
    private $imageDataCache = [];

    /**
     * @param string $base64String
     * @return SimpleImage
     * @throws Exception
     * @author Nick <weist.wei@gmail.com>
     * @date 2021/12/1
     */
    public static function createFromBase64(string $base64String): SimpleImage{
        $newInstance = new static();
        $newInstance->parseImageFromBase64($base64String);
        $newInstance->sourceBase64String = $base64String;

        return $newInstance;
    }

    /**
     * @param string $filePath
     * @return SimpleImage
     * @author Nick <weist.wei@gmail.com>
     * @date 2021/11/20
     */
    public static function createFromFile(string $filePath): SimpleImage{
        $newInstance = new static();
        $newInstance->parseImageFromFile($filePath);
        $newInstance->sourceFilePath = $filePath;
        return $newInstance;
    }


    /**
     * @param string $filePath
     * @return void
     * @author Nick <weist.wei@gmail.com>
     * @date 2022-09-26
     */
    private function parseImageFromFile(string $filePath): void{
        $imageSizeInfo = getimagesize($filePath);

        $this->size = round(filesize($filePath) / 1024, 2);

        $this->width = $imageSizeInfo[0];
        $this->height = $imageSizeInfo[1];
        $this->imageType = $imageSizeInfo[2];
        $this->bits = $imageSizeInfo['bits'];
        $this->mime = $imageSizeInfo['mime'];

        switch($this->imageType){
            case IMAGETYPE_PNG:
                $this->imageResource = imagecreatefrompng($filePath);
                break;
            case IMAGETYPE_JPEG:
                $this->imageResource = imagecreatefromjpeg($filePath);
                break;
            case IMAGETYPE_GIF:
                $this->imageResource = imagecreatefromgif($filePath);
                break;
            case IMAGETYPE_BMP:
                $this->imageResource = imagecreatefrombmp($filePath);
                break;
        }


        $this->extension = SimpleImageConstant::$mimeMatchExtensions[$this->mime];
    }

    /**
     * @param string $base64Encode
     * @return void
     * @author Nick <weist.wei@gmail.com>
     * @date 2022-09-26
     */
    private function parseImageFromBase64(string $base64Encode){
        $imageSourceData = base64_decode($base64Encode);
        $imageSizeInfo = getimagesizefromstring($imageSourceData);

        $this->size = round((strlen(rtrim($base64Encode)) * 0.75) / 1024, 2);

        $this->width = $imageSizeInfo[0];
        $this->height = $imageSizeInfo[1];
        $this->imageType = $imageSizeInfo[2];
        $this->bits = $imageSizeInfo['bits'];
        $this->mime = $imageSizeInfo['mime'];

        $this->imageResource = imagecreatefromstring($imageSourceData);

        $this->extension = SimpleImageConstant::$mimeMatchExtensions[$this->mime];
    }

    /**
     * @param string $filePath
     * @param string $fileName
     * @param null $permissions
     * @return false|string
     * @author Nick <weist.wei@gmail.com>
     * @date 2021/11/30
     */
    public function outputPng(string $filePath, string $fileName, $permissions = null){
        $fileName = $filePath . DIRECTORY_SEPARATOR . $fileName . '.png';
        return $this->save($this->imageResource, $fileName, IMAGETYPE_PNG, $permissions);
    }

    /**
     * @param string $filePath
     * @param string $fileName
     * @param null $permissions
     * @return false|string
     * @author Nick <weist.wei@gmail.com>
     * @date 2021/11/30
     */
    public function outputJpeg(string $filePath, string $fileName, $permissions = null){
        $fileName = $filePath . DIRECTORY_SEPARATOR . $fileName . '.jpeg';
        return $this->save($this->imageResource, $fileName, IMAGETYPE_JPEG, $permissions);
    }

    /**
     * @param string $filePath
     * @param string $fileName
     * @param null $permissions
     * @return false|string
     * @author Nick <weist.wei@gmail.com>
     * @date 2021/11/30
     */
    public function outputGif(string $filePath, string $fileName, $permissions = null){
        $fileName = $filePath . DIRECTORY_SEPARATOR . $fileName . '.gif';
        return $this->save($this->imageResource, $fileName, IMAGETYPE_GIF, $permissions);
    }

    /**
     * @param string $filePath
     * @param string $fileName
     * @param null $permissions
     * @return false|string
     * @author Nick <weist.wei@gmail.com>
     * @date 2021/11/30
     */
    public function outputBmp(string $filePath, string $fileName, $permissions = null){
        $fileName = $filePath . DIRECTORY_SEPARATOR . $fileName . '.bmp';
        return $this->save($this->imageResource, $fileName, IMAGETYPE_BMP, $permissions);
    }

    /**
     * @return string
     * @author Nick
     * @date 2022/3/16
     */
    public function outputBase64(): string{
        ob_start();
        imagepng($this->imageResource);
        $base64string = ob_get_contents();
        ob_end_clean();
        return empty($base64string)
            ? ""
            : 'data:image/png;base64,' . base64_encode($base64string);
    }

    /**
     * @param $image
     * @param string $filePathWithName
     * @param int $imageType
     * @param null $permissions
     * @return false|string
     * @author Nick <weist.wei@gmail.com>
     * @date 2021/11/30
     */
    private function save($image, string $filePathWithName, int $imageType = IMAGETYPE_PNG, $permissions = null){
        $status = false;
        switch($imageType){
            case IMAGETYPE_PNG:
                $status = imagepng($image, $filePathWithName);
                break;
            case IMAGETYPE_JPEG:
                $status = imagejpeg($image, $filePathWithName);
                break;
            case IMAGETYPE_GIF;
                $status = imagegif($image, $filePathWithName);
                break;
            case IMAGETYPE_BMP:
                $status = imagebmp($image, $filePathWithName);
                break;
        }
        if($permissions != null){
            chmod($filePathWithName, $permissions);
        }
        return $status ? basename($filePathWithName) : false;
    }

//    /**
//     * @param int $width
//     * @param int $height
//     * @author Nick <weist.wei@gmail.com>
//     * @date 2021/11/30
//     */
//    public function resizeFromWidthAndHeight(int $width, int $height){
//        $newImage = imagecreatetruecolor($width, $height);
//        imagecopyresampled($newImage, $this->imageResource, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
//
//        $filePath = public_path('/uploads') . DIRECTORY_SEPARATOR . uniqid() . '-' . time() . '.png';
//
//        $this->save($newImage, $filePath, IMAGETYPE_PNG);
//    }

    /**
     * @param int $scale
     * @return SimpleImage
     * @author Nick <weist.wei@gmail.com>
     * @date 2021/12/3
     */
    public function resizeFromScale(int $scale): SimpleImage{
        $newWidth = $this->getWidth() * $scale / 100;
        $newHeight = $this->getHeight() * $scale / 100;
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($newImage, $this->imageResource, 0, 0, 0, 0, $newWidth, $newHeight, $this->getWidth(), $this->getHeight());

        $this->imageResource = $newImage;
        return $this;
    }

    /**
     * @return false|SimpleImage
     * @throws Exception
     * @author Nick
     * @date 2022/3/16
     */
    public function newImage(){
        if(empty($this->sourceBase64String) == false){
            return static::createFromBase64($this->sourceBase64String);
        }
        if(empty($this->sourceFilePath) == false){
            return static::createFromFile($this->sourceFilePath);
        }
        return false;
    }

    /**
     * @param int $imageType
     * @param bool $overWrite
     * @return false|mixed|string
     * @author Nick <weist.wei@gmail.com>
     * @date 2021/12/3
     */
    public function getImageData(int $imageType = IMAGETYPE_PNG, bool $overWrite = false){
        if(empty($this->imageDataCache[$imageType]) || $overWrite){
            ob_start();
            switch($imageType){
                case IMAGETYPE_PNG:
                    imagepng($this->imageResource);
                    break;
                case IMAGETYPE_JPEG:
                    imagejpeg($this->imageResource);
                    break;
                case IMAGETYPE_GIF:
                    imagegif($this->imageResource);
                    break;
                case IMAGETYPE_BMP:
                    imagebmp($this->imageResource);
                    break;
            }
            $this->imageDataCache[$imageType] = ob_get_clean();
        }
        return $this->imageDataCache[$imageType];
    }

    /**
     * @return int
     * @author Nick <weist.wei@gmail.com>
     * @date 2021/11/20
     */
    public function getWidth(): int{
        return $this->width;
    }

    /**
     * @return int
     * @author Nick <weist.wei@gmail.com>
     * @date 2021/11/20
     */
    public function getHeight(): int{
        return $this->height;
    }

    /**
     * @return int
     * @author Nick <weist.wei@gmail.com>
     * @date 2021/11/20
     */
    public function getImageType(): int{
        return $this->imageType;
    }

    /**
     * @return int
     * @author Nick <weist.wei@gmail.com>
     * @date 2021/11/20
     */
    public function getBits(): int{
        return $this->bits;
    }

    /**
     * @return string
     * @author Nick <weist.wei@gmail.com>
     * @date 2021/11/20
     */
    public function getMime(): string{
        return $this->mime;
    }

    /**
     * @return float
     * @author Nick
     * @date 2022/3/16
     */
    public function getSize(): float{
        return $this->size;
    }

    /**
     * @return string
     * @author Nick <weist.wei@gmail.com>
     * @date 2021/11/20
     */
    public function getExtension(): string{
        return $this->extension;
    }

}
