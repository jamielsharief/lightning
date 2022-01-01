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

namespace Lightning\Translator\MessageLoader;

use Psr\SimpleCache\CacheInterface;
use Lightning\Translator\MessageLoaderInterface;
use Lightning\Translator\Exception\MessageFileNotFound;

/**
 * PO File loader - A simple PO file loader which only supports Basic Syntax (msgid + msgstr) and string wrapping. This is for
 * working with PO files and ICU message format.
 */
class PoMessageLoader implements MessageLoaderInterface
{
    private string $path;
    private ?CacheInterface $cache = null;

    /**
     * Constructor
     *
     * @param CacheInterface|null $cache
     */
    public function __construct(string $path, ?CacheInterface $cache = null)
    {
        $this->path = $path;
        $this->cache = $cache;
    }

    /**
     * @see http://pology.nedohodnik.net/doc/user/en_US/ch-poformat.html
     *
     * @param string $path
     * @return array
     */
    public function load(string $domain, string $locale): array
    {
        $path = sprintf('%s/%s.%s.po', $this->path, $domain, $locale);

        if (! file_exists($path)) {
            throw new MessageFileNotFound(sprintf('Message file `%s` not found', $path));
        }

        $key = md5($path);

        if ($this->cache && $this->cache->has($key)) {
            return $this->cache->get($key);
        }

        $data = $this->parse($path);

        if ($this->cache) {
            $this->cache->set($key, $data);
        }

        return $data;
    }

    /**
     * Parse the PO file get the key = value
     *
     * @param string $path
     * @return array
     */
    private function parse(string $path): array
    {
        $data = [];
        $id = $value = '';

        $parsing = null;

        $stream = fopen($path, 'r');
        while ($line = fgets($stream)) {
            $line = trim($line);

            // only parse valid lines, must include space to exclude
            if (substr($line, 0, 6) === 'msgid ') {
                if ($parsing === 'value') {
                    $data[$id] = $value;
                    $id = $value = '';
                    $parsing = null;
                }
                $parsing = 'id';
                $id .= trim(substr($line, 6), '"');
            } elseif (substr($line, 0, 7) === 'msgstr ') {
                $parsing = 'value';
                $value .= trim(substr($line, 7), '"');
            } elseif (substr($line, 0, 1) === '"') {
                $line = trim($line, '"');
                if ($parsing === 'id') {
                    $id .= $line;
                } elseif ($parsing === 'value') {
                    $value .= $line;
                }
            }
        }
        fclose($stream);

        // add last line
        if ($value) {
            $data[$id] = $value;
        }

        return $data;
    }
}
