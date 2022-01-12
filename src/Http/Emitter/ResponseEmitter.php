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

namespace Lightning\Http\Emitter;

use Psr\Http\Message\ResponseInterface;

class ResponseEmitter
{
    /**
     * Emits a response
     *
     * @param ResponseInterface $response
     * @return void
     */
    public function emit(ResponseInterface $response): void
    {
        $filename = null;
        $line = 0;

        if (headers_sent($filename, $line)) {
            trigger_error(sprintf('Headers were already sent in %s on line %s', $filename, $line), E_USER_WARNING);
        }

        $this->sendHeader(sprintf('HTTP/%s %s %s', $response->getProtocolVersion(), $response->getStatusCode(), $response->getReasonPhrase()));

        $cookies = [];
        foreach ($response->getHeaders() as $key => $value) {
            if (strtolower($key) === 'set-cookie') {
                $cookies = $value;

                continue;
            }
            $this->sendHeader(sprintf('%s: %s', $key, $response->getHeaderLine($key)));
        }

        foreach ($cookies as $cookie) {
            $this->sendHeader(sprintf('Set-Cookie: %s', $cookie));
        }

        // ignore no content or not modified response
        if (! in_array($response->getStatusCode(), [204,304])) {
            echo (string) $response->getBody();
        }

        $this->exit();
    }

    /**
     * Sends a header
     *
     * @param string $header
     * @return void
     */
    protected function sendHeader(string $header): void
    {
        header($header);
    }

    /**
     * Exit te
     *
     * @return void
     */
    protected function exit(): void
    {
        exit();
    }
}
