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

class InitializeEvent
{
    public function __construct(protected AbstractController $controller)
    {
    }

    /**
     * Get the value of controller
     */
    public function getController(): AbstractController
    {
        return $this->controller;
    }

    /**
     * Set the value of controller
     */
    public function setController(AbstractController $controller): static
    {
        $this->controller = $controller;

        return $this;
    }
}
