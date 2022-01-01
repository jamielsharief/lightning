<?php declare(strict_types=1);

namespace Lightning\Http\Session;

class PhpSession extends AbstractSession
{
    public function __construct()
    {
        // work in CLI e.g. testing or swoole etc
        if (! isset($_SESSION)) {
            $_SESSION = [];
        }
    }
    /**
     * Undocumented function
     *
     * @param string|null $id
     * @return boolean
     */
    public function start(?string $id): bool
    {
        if ($this->isStarted) {
            return false;
        }

        $this->id = $id ?: $this->createId();

        session_id($this->id);

        // Disable the PHP cookie features, credit to @pmjones for this
        $this->isStarted = session_start([
            'use_cookies' => false,
            'use_only_cookies' => false,
            'use_trans_sid' => false
        ]);

        $this->session = $_SESSION ?? [];

        return $this->isStarted;
    }

    public function close(): bool
    {
        if ($this->isStarted === false) {
            return false;
        }

        //  I remember there were issues with overwriting the $_SESSION variable
        $removed = array_diff(array_keys($_SESSION), array_keys($this->session));

        foreach ($this->session as $key => $value) {
            $_SESSION[$key] = $value;
        }

        foreach ($removed as  $key) {
            unset($_SESSION[$key]);
        }
        $closed = session_write_close();

        $this->isStarted = $closed === false;

        return $this->isRegenerated ? $this->regenerateSessionData() : $closed;
    }

    /**
     * Copy session data to new ID
     *
     * @return boolean
     */
    private function regenerateSessionData(): bool
    {
        $this->isRegenerated = false;
        $session = $_SESSION; // data still seems to be here
        $this->start($this->id); // start session with new session id
        $this->session = $session; // kansas city shuffle

        return $this->close(); // save
    }
}
