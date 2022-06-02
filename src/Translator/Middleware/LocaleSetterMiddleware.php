<?php declare(strict_types=1);
/**
 * LightningPHP
 * Copyright 2021 - 2022 Jamiel Sharief.
 *
 * Licensed under The MIT License
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * @copyright   Copyright (c) Jamiel Sharief
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */

namespace Lightning\Translator\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Lightning\Translator\TranslatorInterface;

/**
 * LocaleSetterMiddleware
 */
class LocaleSetterMiddleware implements MiddlewareInterface
{
    private TranslatorInterface $translator;

    /**
     * Constructor
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Gets the locale attribute from the Request and sets this on the translator if available
     * @TODO: Locale from URL Middleware
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $locale = $request->getAttribute('locale');

        if ($locale) {
            $this->translator->setLocale($locale);
        }

        return $handler->handle($request);
    }
}
