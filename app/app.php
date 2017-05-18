<?php

use Silex\Application;

use pwgram\Model\Services\PdoMapper;

/**
 *
 * lliurament final
 *
 */

$app = new Application();

$app['app.name'] = 'PWGram';

$app['objects_json_parser'] = function () {

    return new \pwgram\Model\Services\ObjectsJsonParser();
};



return $app;
