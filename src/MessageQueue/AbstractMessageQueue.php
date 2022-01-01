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

namespace Lightning\MessageQueue;

class AbstractMessageQueue
{
    /**
     * Serialize an object for messaging, message hash must be unique for each item even if object is the same
     *
     * @param object $object
     * @return string
     */
    protected function serialize(object $object): string
    {
        return serialize([
            'i' => bin2hex(random_bytes(16)),
            'o' => $object
        ]);
    }

    /**
     * Undocumented function
     *
     * @param string $string
     * @return object
     */
    protected function unserialize(string $string): object
    {
        return unserialize($string)['o'];
    }
}
