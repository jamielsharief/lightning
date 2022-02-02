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

use RuntimeException;
use Lightning\Translator\MessageLoaderInterface;
use Lightning\Translator\Exception\MessageFileNotFound;

/**
 * PO File loader - A simple PO file loader which only supports Basic Syntax (msgid + msgstr) and string wrapping. This is for
 * working with PO files and ICU message format.
 */
class PoMessageLoader implements MessageLoaderInterface
{
    private string $path;
    private string $cachedPath;

    /**
     * Constructor
     *
     * @param string $path
     * @param string $cachedPath
     */
    public function __construct(string $path, string $cachedPath)
    {
        $this->path = $path;
        $this->cachedPath = $cachedPath;
    }

    /**
     * Loads the Translations for the domain and locale
     *
     * @see http://pology.nedohodnik.net/doc/user/en_US/ch-poformat.html
     *
     * @param string $domain
     * @param string $locale
     * @return array
     */
    public function load(string $domain, string $locale): array
    {
        $path = sprintf('%s/%s.%s.po', $this->path, $domain, $locale);

        if (! file_exists($path)) {
            throw new MessageFileNotFound(sprintf('Message file `%s` not found', basename($path)));
        }

        return unserialize(file_get_contents($this->cache($path)));
    }

    /**
     * Creates a cached version of the translations and returns the cached name
     *
     * @param string $path
     * @return string
     */
    private function cache(string $path): string
    {
        $cachedPath = $this->cachedPath . '/' . md5($path);

        $isCached = file_exists($cachedPath) && filemtime($cachedPath) > filemtime($path);
        if (! $isCached && file_put_contents($cachedPath, serialize($this->parse($path))) === false) {
            throw new RuntimeException('Error caching translations');
        }

        return $cachedPath;
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

            $start = substr($line, 0, 7);

            // only parse valid lines, must include space to exclude
            if ($start === 'msgid "') {
                if ($parsing === 'value') {
                    $data[$id] = $value;
                    $id = $value = '';
                    $parsing = null;
                }
                $parsing = 'id';
                $id .= trim(substr($line, 6), '"');
            } elseif ($start === 'msgstr ') {
                $parsing = 'value';
                $value .= trim(substr($line, 7), '"');
            } elseif (substr($line, 0, 1) === '"') {
                $line = trim($line, '"');
                if ($parsing === 'id') {
                    $id .= $line;
                } elseif ($parsing === 'value') {
                    $value .= $line;
                }
            } elseif ($start === 'msgid_p' || $start === 'msgstr[') {
                $id = null; // cancel msgid parsing if plural is used
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
