<?php

use Lightning\Router\Router;
use Psr\Log\LoggerInterface;
use Lightning\Http\Cookie\Cookies;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use Lightning\Http\Session\PhpSession;
use Lightning\Translator\TranslatorInterface;
use Lightning\Http\ExceptionHandler\ErrorRenderer;
use Lightning\Http\Cookie\Middleware\CookieMiddleware;
use Lightning\Http\Session\Middleware\SessionMiddleware;
use Lightning\Translator\Middleware\LocaleSetterMiddleware;
use Lightning\Http\ExceptionHandler\ExceptionHandlerMiddleware;

/**
 * TODO: Mightaswell have middlestack or just leave this in routes.
 * should not be building objects here i think (below needs to be refactored now and some services adjusted)
 *
 *
 * Configure your middleware here
 * @exampe $router->addMiddleware(new SessionMiddleware);
 */
return function (Router $router, ContainerInterface $container) {
    // $router->middleware(new ExceptionHandlerMiddleware(
    //     __DIR__ . '/../app/View/error', new ErrorRenderer(), new Psr17Factory(), $container->get(LoggerInterface::class)
    //     )
    // );
    $router->middleware(new SessionMiddleware(new PhpSession()));
    $router->middleware(new CookieMiddleware($container->get(Cookies::class)));
    $router->middleware(new LocaleSetterMiddleware($container->get(TranslatorInterface::class)));
};
