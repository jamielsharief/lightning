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

namespace Lightning\Logger;

use Stringable;

class LogMessage implements Stringable
{
    public function __construct(
        public string $message,
        public array $context = [],
    ) {
    }

    /**
     * Interpolates context values into the message placeholders.
     */
    public function toString(): string
    {
        $replace = [];
        foreach ($this->context as $key => $value) {
            if (! is_array($value) && (! is_object($value) || method_exists($value, '__toString'))) {
                $replace['{' . $key . '}'] = (string) $value;
            }
        }

        return strtr($this->message, $replace);
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
