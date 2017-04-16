<?php
use Silex\Provider\FormServiceProvider;


$app->register(new Silex\Provider\TwigServiceProvider(), array(

    'twig.path'=> __DIR__ . '/../../src/View/templates',

));

$app->register(new Silex\Provider\AssetServiceProvider(), array(

    'assets.version' => 'v1',
    'assets.version_format' => '%s?version=%s',
    'assets.named_packages' => array(
        'css' => array('base_path' => '/assets/css'),
        'js' => array('base_path' => '/assets/js'),
        'img' => array('base_urls' => array('http://grup17.com/assets/img')),

    ),

));

$app->register(new Silex\Provider\SessionServiceProvider());


$app->register(new FormServiceProvider());



