<?php

use Silex\Application;

use pwgram\Model\Services\PdoMapper;


$app = new Application();

$app['app.name'] = 'PWGram';

$app['objects_json_parser'] = function () {

    return new \pwgram\Model\Services\ObjectsJsonParser();
};


$app->register(new Silex\Provider\MonologServiceProvider(), array (

    'monolog.logfile'   => __DIR__ . '/../../var/log/prod.log',
));


$app->register(new PdoMapper());



return $app;
