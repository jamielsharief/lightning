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

namespace Lightning\View;

/**
 * A way to extend your view methods
 */
interface ViewExtensionInterface
{
    /**
     * Return an array of function names mapping to methods on this object
     *
     *  [
     *      'format'
     *      'e' => 'escape'
     *  ]
     *
     *  protected function escape($value)
     *
     * @return array
     */
    public function getMethods(): array;
}
