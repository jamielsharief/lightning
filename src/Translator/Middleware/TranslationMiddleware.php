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

class TranslationMiddleware implements MiddlewareInterface
{
    private string $cookieName = 'locale';

    private TranslatorInterface $translator;

    private array $allowedLocales = [];

    /**
     * Constructor
     *
     * @param TranslatorInterface $translator
     * @param array $allowedLocales
     */
    public function __construct(TranslatorInterface $translator, array $allowedLocales = [])
    {
        $this->translator = $translator;
        $this->allowedLocales = $allowedLocales;
    }

    /**
     * Processes the incoming request
     *
     * @see https://www.php.net/manual/en/locale.acceptfromhttp.php
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $locale = $this->getLocaleFromCookie($request) ?: Locale::acceptFromHttp($request->getHeaderLine('Accept-Language'));

        if ($locale) {
            if ($this->allowedLocales) {
                $locale = Locale::lookup($this->allowedLocales, $locale, false);
            }
            $this->translator->setLocale($locale);

            $request = $request
                ->withAttribute('locale', $locale)
                ->withAttribute('language', Locale::getPrimaryLanguage($locale));
        }

        return $handler->handle($request);
    }

    /**
     * TODO: Not sure if this is good idea
     *
     * @param ServerRequestInterface $request
     * @return string|null
     */
    private function getLocaleFromCookie(ServerRequestInterface $request): ?string
    {
        $cookies = $request->getCookieParams();

        return $cookies[$this->cookieName] ?? null;
    }
}
