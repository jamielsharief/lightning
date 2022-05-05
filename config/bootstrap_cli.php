<?php

use Lightning\Core\Config;
use Lightning\Dotenv\Dotenv;
use Lightning\Container\Container;

// Load composer autoload and our autoploader
require __DIR__ . '/autoload.php';

// Load .env
$dotEnv = (new Dotenv(dirname(__DIR__)))->load();

(new \NunoMaduro\Collision\Provider())->register();

$container = new Container(include __DIR__ . '/services.php');

/*$config = new Config(include __DIR__ . '/config.php');
$container->register('config', $config);*/
