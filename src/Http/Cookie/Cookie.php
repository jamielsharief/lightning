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

namespace Lightning\Http\Cookie;

use Stringable;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

/**
 * Cookie
 *
 * @internal isHttpOnly vs getSecure, is secure I am not sure about.
 */
class Cookie implements Stringable
{
    protected string $name;
    protected string $value;

    /**
     * Set to expire when browser closes
     *
     * @var integer
     */
    protected int $maxAge = 0;

    /**
     * By default cookies on whole domain (javascript default is page ownly)
     *
     * @var string
     */
    protected string $path = '/';
    protected string $domain = '';

    protected bool $secure = false;
    protected bool $httpOnly = false;

    protected string $sameSite = '';

    /**
     * Constructor
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie#Directives
     *
     * @param string $name
     * @param string $value
     */
    public function __construct(string $name, string $value = '')
    {
        /// space, tab, ( ) < > @ , ; : \ " / [ ] ? = { }
        if ((bool) preg_match('#[\s\t\(\)\[\]<>@,;:?="/\\\]#', $name)) {
            throw new InvalidArgumentException(sprintf('Invalid cookie name `%s`', $name));
        }
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * Gets the name of this cookie
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the value of this cookie
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Sets the value of this cookie
     *
     * @param string $value
     * @return self
     */
    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Checks if the cookie is marked as HTTP only
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Cookies
     *
     * @return boolean
     */
    public function isHttpOnly(): bool
    {
        return $this->httpOnly;
    }

    /**
     * Set the value of httpOnly
     *
     * @param bool $httpOnly
     *
     * @return self
     */
    public function setHttpOnly(bool $httpOnly): self
    {
        $this->httpOnly = $httpOnly;

        return $this;
    }

    /**
     * Get the value of secure
     *
     * @return bool
     */
    public function getSecure(): bool
    {
        return $this->secure;
    }

    /**
     * Set the value of secure
     *
     * @param bool $secure
     *
     * @return self
     */
    public function setSecure(bool $secure): self
    {
        $this->secure = $secure;

        return $this;
    }

    /**
     * Get the value of path
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Set the value of path
     *
     * @param string $path
     *
     * @return self
     */
    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get the value of domain
     *
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * Set the value of domain
     *
     * @param string $domain
     *
     * @return self
     */
    public function setDomain(string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Get the value of maxAge
     *
     * @return int
     */
    public function getMaxAge(): int
    {
        return $this->maxAge;
    }

    /**
     * Set the value of maxAge
     *
     * @param int $maxAge
     *
     * @return self
     */
    public function setMaxAge(int $maxAge): self
    {
        $this->maxAge = $maxAge;

        return $this;
    }

    /**
     * Get the value of sameSite
     *
     * @return string
     */
    public function getSameSite(): string
    {
        return $this->sameSite;
    }

    /**
     * Set the value of sameSite
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie/SameSite
     *
     * @param string $sameSite
     *
     * @return self
     */
    public function setSameSite(string $sameSite): self
    {
        $this->sameSite = $sameSite;

        return $this;
    }

    /**
     * Gets a string representation of this cookie
     *
     * @return string
     */
    public function toString(): string
    {
        $out = [];

        $out[] = sprintf('%s=%s', $this->name, rawurlencode($this->value));

        if ($this->maxAge !== 0) {
            $out[] = sprintf('max-age=%s', $this->maxAge);
        }

        if (! empty($this->path)) {
            $out[] = sprintf('path=%s', $this->path);
        }

        if (! empty($this->domain)) {
            $out[] = sprintf('domain=%s', $this->domain);
        }

        if (! empty($this->sameSite)) {
            $out[] = sprintf('samesite=%s', $this->sameSite);
        }

        if ($this->secure) {
            $out[] = 'secure';
        }

        if ($this->httpOnly) {
            $out[] = 'httponly';
        }

        return implode('; ', $out);
    }

    /**
     * Stringable
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Adds this Cookie to a response
     *
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function addToResponse(ResponseInterface $response): ResponseInterface
    {
        return $response->withAddedHeader('Set-Cookie', $this->toString());
    }
}
