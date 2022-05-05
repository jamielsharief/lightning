<?php

use Lightning\Dotenv\Dotenv;

require dirname(__DIR__) . '/config/autoload.php';
(new Dotenv(dirname(__DIR__)))->load();
