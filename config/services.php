<?php

use Nyholm\Psr7\Response;
use Lightning\Router\Router;

use Psr\Log\LoggerInterface;
use Lightning\Autowire\Autowire;
use Lightning\Logger\FileLogger;
use function Lightning\Dotenv\env;
use Lightning\Database\PdoFactory;
use Lightning\Event\EventDispatcher;
use Lightning\Translator\Translator;
use Lightning\Event\ListenerRegistry;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use Lightning\Http\Session\PhpSession;
use Psr\Http\Message\ResponseInterface;
use Lightning\Http\Session\SessionInterface;
use Lightning\DataMapper\DataSourceInterface;
use Lightning\Translator\TranslatorInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Lightning\Translator\ResourceBundleFactory;
use Lightning\TemplateRenderer\TemplateRenderer;
use Lightning\Http\Auth\IdentityServiceInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Lightning\DataMapper\DataSource\DatabaseDataSource;
use Lightning\Http\Auth\IdentityService\PdoIdentityService;

/**
 * Here you register the services. Each time a service is requested is recreated unless you use the share method or pass an existing object.
 */

 return [

     EventDispatcherInterface::class => function (ContainerInterface $container) {
         return new EventDispatcher(new ListenerRegistry());
     },

     Router::class => function (ContainerInterface $container) {
         $router = new Router($container, new Autowire($container), new Response());

         $function = include __DIR__ . '/routes.php';
         $function($router);

         return $router;
     },
     LoggerInterface::class => function (ContainerInterface $container) {
         return new FileLogger(dirname(__DIR__) . '/logs/application.log');
     },
     ResponseInterface::class => Response::class,

     PDO::class => function (ContainerInterface $container) {
         $pdoFactory = new PdoFactory();

         return $pdoFactory->create(env('DB_URL'), env('DB_USERNAME'), env('DB_PASSWORD'),true);
     },
     TemplateRenderer::class => function (ContainerInterface $container) {

        // __DIR__ . '/../tmp/cache')
         return new TemplateRenderer(__DIR__ . '/../app/View');
     },
     TranslatorInterface::class => function (ContainerInterface $container) {
         $bundleFactory = new ResourceBundleFactory(__DIR__ .'/../app/Locales');

         return new Translator($bundleFactory, 'en_US');
     },
     DataSourceInterface::class => DatabaseDataSource::class,
     ResponseFactoryInterface::class => Psr17Factory::class,
     IdentityServiceInterface::class => function (ContainerInterface $container) {
         return (new PdoIdentityService($container->get(PDO::class)))
             ->setTable('identities')
             ->setIdentifierName('username');
     },
     SessionInterface::class => PhpSession::class
 ];
