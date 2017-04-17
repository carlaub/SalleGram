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
    public function saveImage($idUser, $extension, $path) {
        //TODO: redimension

        $pathImgSave = "../web/assets/img/profile_img/" . $idUser . ".". $extension;
        move_uploaded_file($path, $pathImgSave);
        $this->resizingProfileImage($idUser, $extension, $pathImgSave);
    }

    /**
     * Resizing profile image. Default 200x200.
     * @param $idUser
     * @param $extension
     * @param $path
     */
    public function resizingProfileImage($idUser, $extension, $pathImgSave) {
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