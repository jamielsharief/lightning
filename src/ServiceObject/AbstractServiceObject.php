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

namespace Lightning\ServiceObject;

/**
 * Service Object
 *
 * Command Pattern: "an object is used to encapsulate all information needed to perform an action or trigger an event"
 *
 * Idea for type hiting, create an extra method
 *
 * public function with(string $name, string $url): self
 * {
 *     $params = new Params(['name' => $name,'url' => $url]);
 *
 *     return $this->withParams($params);
 *  }
 *
 *
 */
abstract class AbstractServiceObject implements ServiceObjectInterface
{
    private ?Params $params = null;

    /**
     * A hook that is called before execute when the Service Object is run.
     *
     * @return void
     */
    protected function initialize(): void
    {
    }
    /**
     * Returns a new instance with the Params object set
     *
     * @param Params $params
     * @return static
     */
    public function withParams(Params $params): self
    {
        $service = clone $this;
        $service->params = $params;

        return $service;
    }

    /**
     * Runs the Service Object
     *
     * @return Result
     */
    public function run(): Result
    {
        $this->initialize();

        $params = $this->params ?? new Params();
        $result = new Result();

        return call_user_func_array([$this,'execute'], [$params,$result]);
    }

    /**
     * Make this a callable
     *
     * @return Result
     */
    public function __invoke(): Result
    {
        return $this->run();
    }
}
