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

namespace Lightning\ServiceObject;

/**
 * Service Object
 *
 * Command Pattern: "an object is used to encapsulate all information needed to perform an action or trigger an event"
 *
 * IDEA: for type hinting, create an extra method
 *
 * public function with(string $name, string $url): static
 * {
 *     $params = new Params(['name' => $name,'url' => $url]);
 *
 *     return $this->withParams($params);
 *  }
 */
abstract class AbstractServiceObject implements ServiceObjectInterface
{
    private array $params = [];

    /**
     * A hook that is called before execute when the Service Object is run.
     */
    protected function initialize(): void
    {
    }

    /**
     * The Service Object logic that will be executed when it is run
     */
    abstract protected function execute(Params $params): Result;

    /**
     * Gets the params that will be passed when run
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Returns a new instance with the parameters set
     */
    public function withParams(array $params): static
    {
        $service = clone $this;
        $service->params = $params;

        return $service;
    }

    /**
     * Runs the Service Object
     */
    public function run(): Result
    {
        $this->initialize();

        return $this->execute(new Params($this->params));
    }

    /**
     * Make this a callable
     */
    public function __invoke(): Result
    {
        return $this->run();
    }
}
