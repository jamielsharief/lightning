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

namespace Lightning\Controller;

use Psr\Http\Message\ResponseInterface;
use Lightning\Controller\Event\InitializeEvent;
use Lightning\Controller\Event\AfterRenderEvent;
use Lightning\TemplateRenderer\TemplateRenderer;
use Lightning\Controller\Event\BeforeRenderEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Lightning\Controller\Event\AfterRedirectEvent;
use Lightning\Controller\Event\BeforeRedirectEvent;

/**
 * Abstract Controller
 *
 * @internal design has been changed with hook methods added rather than hard coding events etc, these can be overridden with a trait to get
 * the behavior that you want, eg. PSR-14 events
 */
abstract class AbstractEventsController extends AbstractController
{
    protected EventDispatcherInterface $eventDispatcher;

    /**
     * Constructor
     */
    public function __construct(TemplateRenderer $templateRenderer, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($templateRenderer);
        $this->eventDispatcher->dispatch(new InitializeEvent($this));
    }

    /**
     * Before render hook
     */
    public function beforeRender(): ?ResponseInterface
    {
        return $this->eventDispatcher->dispatch(new BeforeRenderEvent($this, $this->request))->getResponse();
    }

    /**
     * After render hook
     */
    public function afterRender(ResponseInterface $response): ResponseInterface
    {
        return $this->eventDispatcher->dispatch(new AfterRenderEvent($this, $this->request, $response))->getResponse();
    }

    /**
     * Before redirect hook
     */
    public function beforeRedirect(string $url): ?ResponseInterface
    {
        return $this->eventDispatcher->dispatch(new BeforeRedirectEvent($this, $url, $this->request))->getResponse();
    }

    /**
     * After redirect hook
     */
    public function afterRedirect(ResponseInterface $response): ResponseInterface
    {
        return $this->eventDispatcher->dispatch(new AfterRedirectEvent($this, $this->request, $response))->getResponse();
    }
}
