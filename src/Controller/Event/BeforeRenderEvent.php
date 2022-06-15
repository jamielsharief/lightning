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

namespace Lightning\Controller\Event;

use Psr\Http\Message\ResponseInterface;
use Lightning\Controller\AbstractController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\EventDispatcher\StoppableEventInterface;

final class BeforeRenderEvent implements StoppableEventInterface
{
    use ControllerEventTrait;
    private bool $stopped = false;

    public function __construct(AbstractController $controller, ?ServerRequestInterface $request = null, ?ResponseInterface $response = null)
    {
        $this->controller = $controller;
        $this->response = $response;
        $this->request = $request;
    }

    public function isPropagationStopped(): bool
    {
        return $this->stopped;
    }

    public function stop(): void
    {
        $this->stopped = true;
    }
}