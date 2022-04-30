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

namespace Lightning\Http\Auth\Middleware;

use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractAuthenticationMiddleware
{
    /**
     * By default all paths are authenticated, unless you set a path e.g. /api
     *
     * @var string|null
     */
    protected ?string $path = null;

    /**
     * Array of publicPaths paths e.g. /login /signup
     *
     * @var array
     */
    protected array $publicPaths = [];

    /**
     * Sets the root path where this will be applied
     *
     * @param string $path e.g. /api
     * @return static
     */
    public function setPath(string $path): static
    {
        $this->path = '/' . trim($path, '/');

        return $this;
    }

    /**
     * Get the value of path
     *
     * @return ?string
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * Allows an array of paths
     *
     * @param array $paths
     * @return static
     */
    public function setPublicPaths(array $paths): static
    {
        $this->publicPaths = $paths;

        return $this;
    }

    /**
     * Get the value of publicPaths
     *
     * @return array
     */
    public function getPublicPaths(): array
    {
        return $this->publicPaths;
    }

    /**
     * Check if the path requires authentication
     *
     * @param ServerRequestInterface $request
     * @return boolean
     */
    protected function requiresAuthentication(ServerRequestInterface $request): bool
    {
        $subject = $request->getUri()->getPath();

        if ($this->path) {
            // if a path is provided and it does not start with the pattern  the request then ignore
            $pattern = str_replace('/', '\/', '/' . trim($this->path, '/'));
            if ((bool) preg_match("#^{$pattern}#", $subject) === false) {
                return false;
            }
        }

        // do exact match against full subject
        foreach ($this->publicPaths as $path) {
            $pattern = str_replace('/', '\/', '/' . trim($path, '/'));
            if ((bool) preg_match("#^{$pattern}$#", $subject)) {
                return false;
            }
        }

        return true;
    }
}
