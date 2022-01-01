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

namespace Lightning\Http\Session;

// TODO: come back to this
class Flash
{
    private SessionInterface $session;

    /**
     * Constructor
     *
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Sets a message or messages
     *
     * @param string $key
     * @param string|array $message or messages
     * @return self
     */
    public function set(string $key, $message): self
    {
        $flashed = $this->session->get('flash', []);
        $flashed[$key] = $message;
        $this->session->set('flash', $flashed);

        return $this;
    }

    /**
     * Checks if the flash messages are stored
     *
     * @param string $key
     * @return boolean
     */
    public function has(string $key): bool
    {
        $flashed = $this->session->get('flash', []);

        return isset($flashed[$key]);
    }

    /**
     * Gets the flash message and removes from the session
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $flashed = $this->session->get('flash', []);

        $out = $default;

        if (isset($flashed[$key])) {
            $out = $flashed[$key];
            unset($flashed[$key]);

            $this->session->set('flash', $flashed);
        }

        return $out;
    }

    /**
     * Gets the keys in flash session
     *
     * @return array
     */
    public function getKeys(): array
    {
        return array_keys($this->session->get('flash', []));
    }
}
