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

use Lightning\Controller\AbstractController;

final class InitializeEvent
{
    private AbstractController $controller;

    public function __construct(AbstractController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Get the Controller
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * set the cController
     */
    public function setController(AbstractController $controller): static
    {
        $this->controller = $controller;

        return $this;
    }
}
