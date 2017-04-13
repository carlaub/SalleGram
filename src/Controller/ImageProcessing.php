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
    }

}