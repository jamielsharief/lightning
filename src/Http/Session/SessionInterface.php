<?php declare(strict_types=1);

namespace Lightning\Http\Session;

interface SessionInterface
{
    /**
     * Sets a value in the session
     *
     * @param string $key
     * @param mixed $value
     * @return self:void
     */
    public function set(string $key, $value);

    /**
     * Gets a value from the session
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * Removes a value from the session
     *
     * @param string $key
     * @return void
     */
    public function unset(string $key): void;

    /**
     * Checks if the session has the key
     *
     * @param string $key
     * @return boolean
     */
    public function has(string $key): bool;

    /**
     * Clears the contents of the session
     *
     * @return void
     */
    public function clear(): void;

    /**
     * Destroy the current session
     *
     * @return void
     */
    public function destroy(): void;

    /**
     * Starts a session and loads data from storage
     *
     * @param string|null $sessionId
     * @return boolean
     */
    public function start(?string $sessionId): bool;

    /**
     * Closes a session and writes to storage
     *
     * @return boolean
     */
    public function close(): bool;

    /**
     * Get the session Id
     *
     * @param string|null $sessionId
     * @return string|null
     */
    public function getId(): ?string;

    /**
     * Informs the session object to regenerate the session id for the existing session data
     *
     * @return boolean
     */
    public function regenerateId(): bool;
}
