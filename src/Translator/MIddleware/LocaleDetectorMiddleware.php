<?php declare(strict_types=1);
/**
 * LightningPHP
 * Copyright 2021 Jamiel Sharief.
 *
 * Licensed under The MIT License
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * @copyright   Copyright (c) Jamiel Sharief
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */

namespace Lightning\Translator\Middleware;

use Locale;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * LocaleDetectorMiddleware
 */
class LocaleDetectorMiddleware implements MiddlewareInterface
{
    private string $defaultLocale;
    private array $locales = [];

    /**
     * Constructor
     *
     * @param string $defaultLocale
     * @param array $locales
     */
    public function __construct(string $defaultLocale, array $locales = [])
    {
        $this->defaultLocale = $defaultLocale;
        $this->locales = $locales;
    }

    /**
     * Detect the locale from the Request headers
     *
     * @see https://www.php.net/manual/en/locale.acceptfromhttp.php
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $locale = Locale::acceptFromHttp($request->getHeaderLine('Accept-Language'));

        if ($locale && $this->locales) {
            $locale = Locale::lookup($this->locales, $locale, false);
        }

        return $handler->handle($request->withAttribute('locale', $locale ?: $this->defaultLocale));
    }
}
