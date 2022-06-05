<?php

use Nyholm\Psr7\Response;
use Lightning\Router\Router;
use Psr\Log\LoggerInterface;
use Lightning\Http\Cookie\Cookies;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use Lightning\Http\Session\SessionInterface;
use Lightning\Translator\TranslatorInterface;
use Lightning\Http\Auth\IdentityServiceInterface;
use Lightning\Http\ExceptionHandler\ErrorRenderer;
use Lightning\Http\Cookie\Middleware\CookieMiddleware;
use Lightning\Http\Middleware\CsrfProtectionMiddleware;
use Lightning\Http\Session\Middleware\SessionMiddleware;
use Lightning\Translator\Middleware\LocaleSetterMiddleware;
use Lightning\Http\Auth\PasswordHasher\BcryptPasswordHasher;
use Lightning\Http\ExceptionHandler\ExceptionHandlerMiddleware;
use Lightning\Http\Auth\Middleware\FormAuthenticationMiddleware;

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
    $router->middleware(new SessionMiddleware($container->get(SessionInterface::class)));
    $router->middleware(new CookieMiddleware($container->get(Cookies::class)));
    $router->middleware(new LocaleSetterMiddleware($container->get(TranslatorInterface::class)));
    $router->middleware(new CsrfProtectionMiddleware($container->get(SessionInterface::class)));
    // $router->middleware(
    //     (new FormAuthenticationMiddleware($container->get(IdentityServiceInterface::class), new BcryptPasswordHasher(), $container->get(SessionInterface::class), new Response()))
    //         ->setLoginPath('/login')
    //         ->setUsernameField('email')
    //         ->setPasswordField('password')
    //         ->setUnauthenticatedRedirect('/login')
    // );
};
