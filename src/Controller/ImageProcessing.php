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
     * @param $userName
     * @param $path
     */
    public function saveImage($userName, $extension, $path) {
        //TODO: redimension

        $pathImgSave = "../web/assets/img/profile_img/" . $userName . ".". $extension;
        move_uploaded_file($path, $pathImgSave);
        $this->resizingProfileImage($userName, $extension, $pathImgSave);
    }

    /**
     * Resizing profile image. Default 200x200.
     * @param $userName
     * @param $extension
     * @param $path
     */
    public function resizingProfileImage($userName, $extension, $pathImgSave) {
        $imgOriginal = $pathImgSave;
        $imgResized = getimagesize($imgOriginal);
        $height = 200;
        $width = 200;

        //Create new image 200x200
        $new = imagecreatetruecolor($width, $height);
        $source = imagecreatefromjpeg($imgOriginal);
        $images = imagecopyresized($new, $source, 0,0,0,0, $width, $height, $imgResized[0], $imgResized[1]);
        imagecopyresampled($new, $source, 0, 0, $width, $height, 0, 0, 0, 0);

        imagejpeg($new, $pathImgSave);
        imagedestroy($new);
        imagedestroy($source);
    }

}