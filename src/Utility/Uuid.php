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

namespace Lightning\Utility;

/**
 * A RFC-4122 compliant UUID Generator
 *
 * A quick and simple UUID generator slightly adapted from the example in the PHP manual.
 *
 * @see https://en.wikipedia.org/wiki/Universally_unique_identifier#Version_4_.28random.29
 * @see https://www.php.net/manual/en/function.com-create-guid.php
 * @see https://datatracker.ietf.org/doc/html/rfc4122
 */
class Uuid
{
    /**
     * Regex pattern
     */
    public const PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/';

    /**
     * Generates a RFC-4122 compliant UUID v4
     *
     * @return string
     */
    public function generate(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr(ord($bytes[6]) & 0x0f | 0x40); // set version to 0100
        $bytes[8] = chr(ord($bytes[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }
}
