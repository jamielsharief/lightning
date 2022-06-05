<?php

use Lightning\Dotenv\Dotenv;
use Lightning\Router\Router;
use function Lightning\Dotenv\env;
use Lightning\Container\Container;
use Lightning\Http\Emitter\ResponseEmitter;
use Lightning\Translator\TranslatorManager;
use Lightning\Translator\TranslatorInterface;
use Lightning\Http\ExceptionHandler\ErrorHandler;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Load composer autoload and our autoploader
require __DIR__ . '/autoload.php';

// Load .env
$dotEnv = (new Dotenv(dirname(__DIR__)))->load();

if (filter_var(env('APP_DEBUG', true), FILTER_VALIDATE_BOOLEAN) === true) {
    $whoops = new \Whoops\Run();
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
    $whoops->register();
} else {
    (new ErrorHandler())->register();
}

$container = (new Container(include __DIR__ . '/services.php'))
    ->enableAutoConfigure()
    ->enableAutowiring();

/**
 * Configure the Translator manager so the __ function can be used
 */
TranslatorManager::set($container->get(TranslatorInterface::class));

/*
$config = new Config(include __DIR__ . '/config.php');
$container->add('config', $config);
*/

$router = $container->get(Router::class);

$function = include __DIR__ . '/middleware.php';
$function($router, $container);

$psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
$creator = new \Nyholm\Psr7Server\ServerRequestCreator($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
$request = $creator->fromGlobals();

(new ResponseEmitter())->emit($router->dispatch($request));

// echo '<p>' . (microtime(true) - START_TIME) . ' ms' . '</p>';
// echo '<p>' . (memory_get_peak_usage() / 1024 / 1024). ' MB' . '</p>';
