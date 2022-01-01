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

namespace Lightning\Controller\Event;

use Lightning\Controller\AbstractController;

abstract class AbstractControllerEvent
{
    protected AbstractController $controller;
    protected array $data = [];

    /**
     * Constructor
     *
     * @param AbstractController $controller
     * @param array $data
     */
    public function __construct(AbstractController $controller, array $data = [])
    {
        $this->controller = $controller;
        $this->data = $data;
    }

    /**
     * Gets the Controller for this Event
     *
     * @return AbstractController
     */
    public function getController(): AbstractController
    {
        return $this->controller;
    }

    /**
     * Gets the data/payload for this Event (if any)
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
