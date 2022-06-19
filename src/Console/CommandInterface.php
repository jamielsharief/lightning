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

namespace Lightning\Console;

interface CommandInterface
{
    /**
     * Gets the name of the command
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Gets the description of the command
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Runs the command
     *
     * @param array $args
     * @return integer
     */
    public function run(array $args): int ;
}
