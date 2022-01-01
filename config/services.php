<?php

use Lightning\View\View;
use Nyholm\Psr7\Response;

use Lightning\Router\Router;
use Psr\Log\LoggerInterface;
use App\View\ApplicationView;
use Lightning\Log\FileLogger;
use Lightning\Autowire\Autowire;
use Lightning\View\ViewCompiler;
use Lightning\Database\PdoFactory;
use Lightning\Event\EventDispatcher;
use Lightning\Translator\Translator;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Lightning\DataMapper\DataSourceInterface;
use Lightning\Translator\TranslatorInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Lightning\DataMapper\DataSource\DatabaseDataSource;
use Lightning\Translator\MessageLoader\PhpMessageLoader;

/**
 * Here you register the services. Each time a service is requested is recreated unless you use the share method or pass an existing object.
 */

 return [
     EventDispatcherInterface::class => function (ContainerInterface $container) {
         return new EventDispatcher();
     },

     Router::class => function (ContainerInterface $container) {
         $router = new Router($container, $container->get(EventDispatcherInterface::class), new Psr17Factory(), new Autowire($container));

         $function = include __DIR__ . '/routes.php';
         $function($router);

         return $router;
     },
     LoggerInterface::class => function (ContainerInterface $container) {
         return new FileLogger(dirname(__DIR__) . '/logs/application.log');
     },
     ResponseInterface::class => Response::class,

     View::class => function (ContainerInterface $container) {
         $path = __DIR__ . '/../app/View';

         return new ApplicationView(new ViewCompiler($path, __DIR__ . '/../tmp/cache'), $path);
     },

     PDO::class => function (ContainerInterface $container) {
         $pdoFactory = new PdoFactory();

         return $pdoFactory->create(getenv('DB_URL'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
     },

     // TODO: I want to share this
     TranslatorInterface::class => function (ContainerInterface $container) {
         $loader = new PhpMessageLoader(__DIR__ .'/../app/Locales');

         return new Translator($loader, 'en_US', 'messages');
     },
     DataSourceInterface::class => DatabaseDataSource::class,
     ResponseFactoryInterface::class => Psr17Factory::class

 ];
