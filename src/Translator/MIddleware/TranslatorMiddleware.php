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
use Lightning\Translator\TranslatorInterface;

class TranslatorMiddleware implements MiddlewareInterface
{
    private TranslatorInterface $translator;

    private array $locales = [];

    /**
     * Constructor
     *
     * @param TranslatorInterface $translator
     * @param array $locales
     */
    public function __construct(TranslatorInterface $translator, array $locales = [])
    {
        $this->translator = $translator;
        $this->locales = $locales;
    }

    /**
     * Processes the incoming request
     *
     * @internal This also checks for the `locale` attribute on the request, which can be parsed from the route URL. eg. /blog/en/something and
     *           should only be used for available locales
     *
     * @see https://www.php.net/manual/en/locale.acceptfromhttp.php
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $defaultLocale = $this->translator->getLocale();
        $locale = $request->getAttribute('locale') ?: Locale::acceptFromHttp($request->getHeaderLine('Accept-Language'));

        // lookup
        if ($locale && $this->locales) {
            $locale = Locale::lookup($this->locales, $locale, false);
        }

        if ($locale) {
            $this->translator->setLocale($locale);
        }

        // Sets the final locale and language that will be used during this request
        $request = $request
            ->withAttribute('locale', $locale ?: $defaultLocale)
            ->withAttribute('language', Locale::getPrimaryLanguage($locale ?: $defaultLocale));

        return $handler->handle($request);
    }
}
