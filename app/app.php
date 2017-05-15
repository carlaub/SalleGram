<?php

use Silex\Application;
use pwgram\Controller\homeController;
use pwgram\Controller\formsController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use pwgram\lib\Database\Database;
use pwgram\Controller\CommentsController;
use Doctrine\DBAL\DriverManager;
use pwgram\Model\Services\PdoMapper;
use pwgram\Model\Repository\PdoFollowRepository;
use pwgram\Model\Repository\PdoRepository;

$app = new Application();

$app['app.name'] = 'PWGram';

$app['objects_json_parser'] = function () {

    return new \pwgram\Model\Services\ObjectsJsonParser();
};


/**
 * @param Application $pdoRepository
 * @return PdoRepository PdoRPdoFollowRepository
 */
$app['pdo'] = function ($pdoRepository) {

    switch ($pdoRepository) {

        case PdoMapper::PDO_FOLLOW:

            return new PdoFollowRepository();
    }

    return new PdoFollowRepository();
};

return $app;
