<?php

$loader = require_once __DIR__.'/../vendor/autoload.php';

// Register Namespace
$loader->add('Poeticus', __DIR__.'/../src');

$app = new Silex\Application();

require __DIR__.'/../app/config/dev.php';
require __DIR__.'/../src/app.php';
require __DIR__.'/../src/routes.php';




$app->run();