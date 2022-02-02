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

namespace Lightning\Translator\MessageLoader;

use Lightning\Translator\MessageLoaderInterface;
use Lightning\Translator\Exception\MessageFileNotFound;

class PhpMessageLoader implements MessageLoaderInterface
{
    private string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function load(string $domain, string $locale): array
    {
        $path = sprintf('%s/%s.%s.php', $this->path, $domain, $locale);

        if (file_exists($path)) {
            return include $path;
        }

        throw new MessageFileNotFound(sprintf('Message file `%s` not found', $path));
    }
}
