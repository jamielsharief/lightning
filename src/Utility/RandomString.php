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
 * A secure random string generator
 */
class RandomString
{
    public const NUMERIC = '0123456789';
    public const HEX = '0123456789abcdef';
    public const BASE_36 = '0123456789abcdefghijklmnopqrstuvwxyz';
    public const BASE_58 = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
    public const BASE_62 = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    public const BASE_64 = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ+/';
    public const BASE_64_SAFE = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_';

    /**
     * @see https://owasp.org/www-community/password-special-characters
     */
    public const SPECIAL = ' !"#$%&\'()*+,-./:;<=>?@[\]^_`{|}~';

    /**
     * Constructor
     *
     * @param string $charset
     */
    public function __construct(private string $charset = self::BASE_62)
    {
    }

    /**
     * Generate a random string
     *
     * @param integer $length
     * @return string
     */
    public function generate(int $length): string
    {
        $max = strlen($this->charset) - 1;

        $out = '';
        for ($i = 0; $i < $length; $i++) {
            $out .= $this->charset[random_int(0, $max)];
        }

        return $out;
    }

    /**
     * Get the value of charset
     *
     * @return string
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * Set the value of charset
     *
     * @param string $charset
     * @return static
     */
    public function setCharset(string $charset): static
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * Undocumented function
     *
     * @param string $charset
     * @return static
     */
    public function withCharset(string $charset): static
    {
        return (clone $this)->setCharset($charset);
    }
}
