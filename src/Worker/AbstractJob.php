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

namespace Lightning\Worker;

use Lightning\Params\Params;

abstract class AbstractJob implements RunnableInterface, RetryableInterface
{
    private int $attempts = 0;

    protected int $maxRetries = 3;
    protected int $delay = 15;

    private ?Params $params;

    /**
     * Hook called when this message is processed
     */
    protected function initialize(): void
    {
    }

    /**
     * Gets the params that will be passed when run
     */
    public function getParameters(): array
    {
        return isset($this->params) ? $this->params->toArray() : [];
    }

    /**
     * Returns a new instance with the parameters set
     */
    public function withParameters(array $parameters): static
    {
        $service = clone $this;
        $service->params = new Params($parameters);

        return $service;
    }

    abstract protected function execute(Params $params): void;

    /**
     * Runs the JOB
     */
    public function run(): void
    {
        $this->initialize();
        $this->execute($this->params ?? new Params());
    }

    /**
     * Instructs the message object that processing failed
     */
    public function fail(): void
    {
        $this->attempts ++;
    }

    /**
     * Gets the number of attempts
     */
    public function attempts(): int
    {
        return $this->attempts;
    }

    /**
     * Gets the maximum number of times this message processing should be retyied
     */
    public function maxRetries(): int
    {
        return $this->maxRetries;
    }

    /**
     * Seconds to wait before retrying
     */
    public function delay(): int
    {
        return $this->delay;
    }
}
