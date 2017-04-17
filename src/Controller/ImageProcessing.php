<?php
namespace pwgram\Controller;

/**
 * Class ImageProcessing
 *
 * This class is destined to image management and processing.
 * Some of its functions are saved the image and resize it.
 * @package pwgram\Controller
 */
class ImageProcessing {
    /**
     * Save image from form in to directory specified
     * @param $idUser
     * @param $path
     */
    public function saveProfileImage($idUser, $extension, $path) {
        $pathImgSave = "../web/assets/img/profile_img/" . $idUser . ".". $extension;
        move_uploaded_file($path, $pathImgSave);
        chmod($pathImgSave, 0777);
        $this->resizingProfileImage($pathImgSave);
    }

    /**
     * @param $idImage
     * @param $extension
     * @param $path
     */
    public function saveUploadImage($idImage, $extension, $path) {

        $pathImgSave = "../web/assets/img/upload_img/" . $idImage . ".". $extension;

        $pathImgSaveLittleSize = "../web/assets/img/upload_img/" . $idImage ."_100x100.". $extension;
        //chmod($pathImgSaveLittleSize, 0777);

        $pathImgSaveLargeSize = "../web/assets/img/upload_img/" . $idImage ."_400x300.". $extension;
        //chmod($pathImgSaveLargeSize, 0777);


        move_uploaded_file($path, $pathImgSave);

        $this->resizingUploadImage($pathImgSave, $pathImgSaveLittleSize, $pathImgSaveLargeSize);
    }

    /**
     * Resizing profile image. Default 200x200.
     * @param $idUser
     * @param $extension
     * @param $path
     */
    public function resizingProfileImage($pathImgSave) {

        $imgOriginal = $pathImgSave;
        $imgResized = getimagesize($imgOriginal);
        $height = 200;
        $width = 200;

        // Create new image 200x200
        $new = imagecreatetruecolor($width, $height);
        $source = imagecreatefromjpeg($imgOriginal);
        $images = imagecopyresized($new, $source, 0,0,0,0, $width, $height, $imgResized[0], $imgResized[1]);
        imagecopyresampled($new, $source, 0, 0, $width, $height, 0, 0, 0, 0);

        imagejpeg($new, $pathImgSave);
        imagedestroy($new);
        imagedestroy($source);
    }

    /**
     * Save upload image in two different size (100x100) and (400x300)
     *
     * @param $pathImgSave
     * @param $pathImgSaveLittleSize
     * @param $pathImgSaveLargeSize
     */
    public function resizingUploadImage($pathImgSave, $pathImgSaveLittleSize, $pathImgSaveLargeSize) {

        // Get original photo size
        $imgResized = getimagesize($pathImgSave);

        // Little size (100x100)
        $new = imagecreatetruecolor(100, 100);
        $source = imagecreatefromjpeg($pathImgSave);

        $images = imagecopyresized($new, $source, 0,0,0,0, 100, 100, $imgResized[0], $imgResized[1]);
        imagecopyresampled($new, $source, 0, 0, 100, 100, 0, 0, 0, 0);

        imagejpeg($new, $pathImgSaveLittleSize);
        imagedestroy($new);

        // Large Size (400x300)
        $new = imagecreatetruecolor(400, 300);

        $images = imagecopyresized($new, $source, 0,0,0,0, 400, 300, $imgResized[0], $imgResized[1]);
        imagecopyresampled($new, $source, 0, 0, 400, 300, 0, 0, 0, 0);

        imagejpeg($new, $pathImgSaveLargeSize);
        imagedestroy($new);

        imagedestroy($source);
        // Delete original image
        unlink($pathImgSave);
    }
}