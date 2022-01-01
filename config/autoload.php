<?php
/**
 * This will be main bootstrap file
 */

use Lightning\Autoloader\Autoloader;

require __DIR__ . '/../vendor/autoload.php';

/**
 * Use our own autoloader
 */
require __DIR__ . '/../src/Autoloader/Autoloader.php';
$autoloader = new Autoloader(dirname(__DIR__));
$autoloader->addNamespaces([
    'App' => 'app',
    'Lightning' => 'src',
    'Lightning\\Test' => 'tests'
]);
$autoloader->register();
