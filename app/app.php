<?php

use Silex\Application;


$app = new Application();

$app['app.name'] = 'PWGram';

$app['objects_json_parser'] = function () {

    return new \pwgram\Model\Services\ObjectsJsonParser();
};



return $app;
