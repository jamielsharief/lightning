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

namespace Lightning\Autoloader;

/**
 * PSR-4 Autoloader
 * @see https://www.php-fig.org/psr/psr-4/
 */
class Autoloader
{
    protected string $directory;
    protected array $prefixes = [];

    /**
     * Construtor
     *
     * @param string $directory
     */
    public function __construct(string $directory)
    {
        $this->directory = rtrim($directory, '/');
    }

    /**
     * Register loader with SPL autoloader stack.
     *
     * @return boolean
     */
    public function register(): bool
    {
        $this->sortPrefixes();

        return spl_autoload_register([$this, 'loadClass']);
    }

    /**
     * Sort prefixes so that the longest prefixes are first and shorter ones are last
     *
     * @return void
     */
    protected function sortPrefixes(): void
    {
        uksort($this->prefixes, function ($a, $b) {
            return strlen($b) <=> strlen($a);
        });
    }

    /**
     * Adds namespaces to be loaded to the autoloader
     *
     * @example
     *
     * $autoloader->addNamespaces([
     *      'Lightning' => 'src',
     *      'Lightning\\Test' => 'tests/TestCase',
     *      'App' => 'app'
     *  ]);
     *
     * @param array $namespaces
     * @return void
     */
    public function addNamespaces(array $namespaces): void
    {
        foreach ($namespaces as $namespace => $baseDirectory) {
            $this->addNamespace($namespace, $baseDirectory);
        }
    }

    /**
     * @example
     *
     * $autoloader->addNamespace('App\\Test','tests/TestCase')
     *
     * @param string $prefix
     * @param string $baseDirectory
     * @return void
     */
    public function addNamespace(string $prefix, string $baseDirectory): void
    {
        $prefix = trim($prefix, '\\') . '\\';
        $this->prefixes[$prefix] = $this->directory . '/' . trim($baseDirectory, '/') . '/';
    }

    /**
     * This is the class loading functionality
     *
     * @param string $class
     * @return boolean
     */
    public function loadClass(string $class): bool
    {
        foreach ($this->prefixes as $prefix => $baseDirectory) {
            $length = strlen($prefix);
            if (strncmp($class, $prefix, $length) === 0) {
                $path = $baseDirectory . str_replace('\\', '/', substr($class, $length)) . '.php';

                return $this->requireFile($path);
            }
        }

        return false;
    }

    /**
     * Requires the file
     *
     * @param string $path
     * @return boolean
     */
    protected function requireFile(string $path): bool
    {
        $fileExists = file_exists($path);
        if ($fileExists) {
            require $path;
        }

        return $fileExists;
    }
}
