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

namespace Lightning\Http\Cookie;

use Psr\Http\Message\ServerRequestInterface;

/**
 * TODO: move to own repo, Cookies and Cookie object perhaps.
 */
class Cookies
{
    protected ?ServerRequestInterface $request;

    protected array $requestCookies = [];
    protected array $responseCookies = [];

    public function __construct(?ServerRequestInterface $request = null)
    {
        $this->request = $request;
        $this->requestCookies = $request ? $request->getCookieParams() : [];
    }

    /**
     * Gets a cookie from the request
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get(string $name, $default = null)
    {
        return array_key_exists($name, $this->requestCookies) ? $this->requestCookies[$name] : $default;
    }

    /**
     * Checks the request has a cookie
     *
     * @param string $name
     * @return boolean
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->requestCookies);
    }

    /**
     * Sets a cookie for deletion
     *
     * @param string $name
     * @return self
     */
    public function delete(string $name): self
    {
        $this->set($name, '', [
            'expires' => time() - 3600
        ]);

        return $this;
    }

    /**
     * Creates a new cookie
     *
     * @param string $name
     * @param string $value
     * @param array $options
     * @return self
     */
    public function set(string $name, string $value, array $options = []): self
    {
        $options += [
            'path' => '/', // path on server
            'domain' => '', // domains cookie will be available on
            'secure' => false, // only send if through https
            'httpOnly' => false, // only available to  HTTP protocol not to javascript
            'expires' => null,
            'sameSite' => '' // lax/strict/none
        ];

        $options['name'] = $name;
        $options['value'] = $value;

        $this->responseCookies[$name] = $options;

        return $this;
    }

    /**
     * Convert the cookies  to be set to an array of headers
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_map(function ($cookie) {
            return $this->cookieToHeaderString($cookie);
        }, $this->responseCookies);
    }

    private function cookieToHeaderString(array $cookie): string
    {
        $out = [];

        $out[] = urlencode($cookie['name']) . '=' . urlencode($cookie['value']);

        if ($cookie['expires'] !== null) {
            $time = is_string($cookie['expires']) ? strtotime($cookie['expires']) : $cookie['expires'];
            $out[] = 'expires=' . gmdate('D, d M Y H:i:s T', $time);
        }

        if (! empty($cookie['path'])) {
            $out[] = 'path=' . $cookie['path'];
        }

        if (! empty($cookie['domain'])) {
            $out[] = 'domain=' . $cookie['domain'];
        }

        if ($cookie['secure']) {
            $out[] = 'secure';
        }

        if ($cookie['httpOnly']) {
            $out[] = 'httponly';
        }

        if ($cookie['sameSite']) {
            $out[] = 'samesite='  . $cookie['sameSite'];
        }

        return implode('; ', $out);
    }
}
