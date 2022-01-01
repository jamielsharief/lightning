<?php

use Lightning\Dotenv\Dotenv;

require dirname(__DIR__) . '/config/autoload.php';
include dirname(__DIR__) . '/src/Dotenv/functions.php'; // TODO: temp
(new Dotenv(dirname(__DIR__)))->load();
