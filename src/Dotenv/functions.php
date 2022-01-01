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

namespace Lightning\Dotenv;

/**
 * Gets a value from the environment
 *
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function env(string $key, $default = null)
{
    $value = $_SERVER[$key] ?? $_ENV[$key] ?? null;

    if ($value === null) {
        $value = getenv($key) ?: null;
    }

    return $value ?: $default;
}
