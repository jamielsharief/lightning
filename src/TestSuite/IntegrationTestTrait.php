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

namespace Lightning\TestSuite;

use Throwable;
use RuntimeException;
use BadMethodCallException;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use PHPUnit\Exception as PHPUnitException;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

/**
 * Integration testing trait for PSR based projects
 */
trait IntegrationTestTrait
{
    protected ServerRequestFactory $serverRequestFactory;
    protected ResponseFactoryInterface $responseFactory;
    protected RequestHandlerInterface $requestHandler;

    protected TestSessionInterface $testSession;

    protected ?ServerRequestInterface $serverRequest = null;
    protected ?ResponseInterface $response = null;

    protected array $env = [];
    protected array $serverParams = [];
    protected array $cookies = [];
    protected array $session = [];
    protected array $files = [];
    protected array $headers = [];

    protected bool $errorHandling = true;

    private array $envBackup = [];

    /**
     * Setup the Integration Testing features, call this from the PHPUnit setUp method. This will set the depdencies
     * and reset the variables.
     *
     * @param ServerRequestFactory $serverRequestFactory Lightning TestSuite ServerRequestFactory object
     * @param ResponseFactoryInterface $responseFactory  PSR-17 Response Factory
     * @param RequestHandlerInterface $requestHandler
     * @param TestSessionInterface $testSession
     * @return void
     */
    public function setupIntegrationTesting(
        ServerRequestFactory $serverRequestFactory, ResponseFactoryInterface $responseFactory, RequestHandlerInterface $requestHandler, TestSessionInterface $testSession
        ): void {
        $this->serverRequestFactory = $serverRequestFactory;
        $this->responseFactory = $responseFactory;
        $this->requestHandler = $requestHandler;
        $this->testSession = $testSession;

        $this->headers = $this->session = $this->cookies = $this->files = $this->serverParams = [];
        $this->errorHandling = true; // Renable for next test

        if (empty($this->envBackup)) {
            $this->envBackup = $_ENV;
        }
    }

    /**
     * Sends a GET request
     *
     * @param string $uri
     * @return ResponseInterface
     */
    protected function get(string $uri): ResponseInterface
    {
        return $this->sendRequest('GET', $uri);
    }

    /**
     * Set a POST request
     *
     * @param string $uri
     * @param array $data
     * @return ResponseInterface
     */
    protected function post(string $uri, array $data = []): ResponseInterface
    {
        return $this->sendRequest('POST', $uri, $data);
    }

    /**
     * Set a PATCH request
     *
     * @param string $uri
     * @param array $data
     * @return ResponseInterface
     */
    protected function patch(string $uri, array $data = []): ResponseInterface
    {
        return $this->sendRequest('PATCH', $uri, $data);
    }

    /**
     * Set a PUT request
     *
     * @param string $uri
     * @param array $data
     * @return ResponseInterface
     */
    protected function put(string $uri, array $data = []): ResponseInterface
    {
        return $this->sendRequest('PUT', $uri, $data);
    }

    /**
     * Sends a DELETE request
     *
     * @param string $uri
     * @return ResponseInterface
     */
    protected function delete(string $uri): ResponseInterface
    {
        return $this->sendRequest('DELETE', $uri);
    }

    /**
     * Sends a HEAD request
     *
     * @param string $uri
     * @return ResponseInterface
     */
    protected function head(string $uri): ResponseInterface
    {
        return $this->sendRequest('HEAD', $uri);
    }

    /**
     * Sends a OPTIONS request
     *
     * @param string $uri
     * @return ResponseInterface
     */
    protected function options(string $uri): ResponseInterface
    {
        return $this->sendRequest('OPTIONS', $uri);
    }

    /**
     *
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return ResponseInterface
     */
    private function sendRequest(string $method, string $uri, array $data = []): ResponseInterface
    {
        if (! isset($this->serverRequestFactory)) {
            throw new BadMethodCallException('You must call setupIntegrationTesting first');
        }

        $this->serverRequest = $this->response = null; // Remove previous

        $this->serverRequest = $this->serverRequestFactory->create($method, $uri, [
            'serverParams' => $this->serverParams,
            'headers' => $this->headers,
            'cookies' => $this->cookies,
            'post' => $data,
            'files' => $this->files
        ]);

        $_ENV = $this->envBackup;
        foreach ($this->env as $key => $value) {
            $_ENV[$key] = $value;
        }

        $this->testSession->clear();
        foreach ($this->session as $key => $value) {
            $this->testSession->set($key, $value);
        }

        try {
            $this->response = $this->requestHandler->handle($this->serverRequest);
        } catch (PHPUnitException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            if (! $this->errorHandling) {
                throw $exception;
            }
            $this->response = $this->responseFactory->createResponse($exception->getCode(), $exception->getMessage());
        }

        return $this->response;
    }

    /**
     * Asserts a specific response code e.g. 200
     *
     *  200 - OK (Success)
     *  400 - Bad Request (Failure - client side problem)
     *  500 - Internal Error (Failure - environment side problem)
     *  401 - Unauthorized
     *  404 - Not Found
     *  403 - Forbidden (For application level permisions)
     *
     * @see https://www.restapitutorial.com/httpstatuscodes.html
     * @param integer $statusCode
     * @return void
     */
    protected function assertResponseCode(int $statusCode): void
    {
        $this->checkRequestWasHandled();

        $this->assertEquals($statusCode, $this->response->getStatusCode(), sprintf('Response code was %d', $statusCode));
    }

    /**
     * Asserts that the response was a redirect
     *
     * @return void
     */
    public function assertRedirect(): void
    {
        $this->assertHeaderSet('Location');
    }

    /**
     * Asserts that the response was a redirect and URL matches
     *
     * @param string|null $url
     * @return void
     */
    public function assertRedirectEquals(string $url): void
    {
        $this->assertHeaderSet('Location');
        $this->assertHeaderEquals('Location', $url);
    }

    /**
     * Asserts that the response was a redirect and URL does not match
     *
     * @param string|null $url
     * @return void
     */
    public function assertRedirectNotEquals(string $url): void
    {
        $this->assertHeaderSet('Location');
        $this->assertHeaderNotEquals('Location', $url);
    }

    /**
     * Asserts that the response was a redirect and contains a substring
     *
     * @param string $needle
     * @return void
     */
    public function assertRedirectContains(string $needle): void
    {
        $this->assertHeaderSet('Location');
        $this->assertHeaderContains('Location', $needle);
    }

    /**
     * Asserts that the response was a redirect and not contains a substring
     *
     * @param string $needle
     * @return void
     */
    public function assertRedirectNotContains(string $needle): void
    {
        $this->assertHeaderSet('Location');
        $this->assertHeaderNotContains('Location', $needle);
    }

    /**
     * Asserts that there was no redirect
     *
     * @return void
     */
    public function assertNoRedirect(): void
    {
        $this->assertHeaderNotSet('Location');
    }

    /**
     * Assert the Response Body equals a string
     *
     * @param string $needle
     * @return void
     */
    protected function assertResponseEquals(string $needle): void
    {
        $this->checkRequestWasHandled();
        $this->assertEquals($needle, (string) $this->response->getBody());
    }

    /**
     * Assert the Response Body not equals a string
     *
     * @param string $needle
     * @return void
     */
    protected function assertResponseNotEquals(string $needle): void
    {
        $this->checkRequestWasHandled();
        $this->assertNotEquals($needle, (string) $this->response->getBody());
    }

    /**
     * Asserts that the Response is a downloadable file.
     *
     * @param string $filename
     * @return void
     */
    protected function assertResponseFile(string $filename): void
    {
        $this->checkRequestWasHandled();
        $header = $this->response->getHeaderLine('Content-Disposition');

        $this->assertNotNull($header, 'Response does not have the `Content-Disposition` header');
        $this->assertStringContainsString('attachment', $header, 'Response content is not downloadable');
        $this->assertStringContainsString('filename=', $header, 'Response content does not include a filename');
        $this->assertStringContainsString('filename=' . $filename, $header, sprintf('Response filename does not match `%s`', $filename));
    }

    /**
     * Assert the Response Body is empty
     *
     * @param string $needle
     * @return void
     */
    protected function assertResponseContains(string $needle): void
    {
        $this->checkRequestWasHandled();
        $this->assertStringContainsString($needle, (string) $this->response->getBody());
    }

    /**
     * Assert the Response Body is empty
     *
     * @return void
     */
    protected function assertResponseEmpty(): void
    {
        $this->checkRequestWasHandled();
        $this->assertEmpty((string) $this->response->getBody());
    }

    /**
    * Assert the Response Body is not empty
    *
    * @return void
    */
    protected function assertResponseNotEmpty(): void
    {
        $this->checkRequestWasHandled();
        $this->assertNotEmpty((string) $this->response->getBody());
    }

    /**
    * Assert the Response Body contains a string
    *
    * @param string $needle
    * @return void
    */
    protected function assertResponseNotContains(string $needle): void
    {
        $this->checkRequestWasHandled();
        $this->assertStringNotContainsString($needle, (string) $this->response->getBody());
    }

    /**
     * Assert the Response Body matches a regular expression
     *
     * @param string $pattern
     * @return void
     */
    protected function assertResponseMatchesRegularExpression(string $pattern): void
    {
        $this->checkRequestWasHandled();
        $this->assertMatchesRegularExpression($pattern, (string) $this->response->getBody());
    }

    /**
    * Assert the Response Body does not match a regular expression
    *
    * @param string $pattern
    * @return void
    */
    protected function assertResponseDoesNotMatchRegularExpression(string $pattern): void
    {
        $this->checkRequestWasHandled();
        $this->assertDoesNotMatchRegularExpression($pattern, (string) $this->response->getBody());
    }

    /**
     * Assets that the response header was set
     *
     * @param string $header
     * @return void
     */
    public function assertHeaderSet(string $header): void
    {
        $this->checkRequestWasHandled();

        $this->assertNotEmpty($this->response->getHeaderLine($header), sprintf('Response does not have the header `%s`', $header));
    }

    /**
     * Assets that the response header was not set
     *
     * @param string $header
     * @return void
     */
    public function assertHeaderNotSet(string $header): void
    {
        $this->checkRequestWasHandled();
        $this->assertEmpty($this->response->getHeaderLine($header), sprintf('Response has the header `%s`', $header));
    }

    /**
     * Assert that response header equals the one provided
     *
     * @param string $header
     * @param string $value
     * @return void
     */
    protected function assertHeaderEquals(string $header, string $value): void
    {
        $this->checkRequestWasHandled();

        $actual = $this->response->getHeaderLine($header);
        $this->assertNotEmpty($actual, sprintf('Response does not have the header `%s`', $header));
        $this->assertEquals($value, $actual, sprintf('Response header `%s` is not equal to `%s`', $header, $value));
    }

    /**
     * Assert that response header equals the one provided
     *
     * @param string $header
     * @param string $value
     * @return void
    */
    protected function assertHeaderNotEquals(string $header, string $value): void
    {
        $this->checkRequestWasHandled();

        $actual = $this->response->getHeaderLine($header);
        $this->assertNotEmpty($actual, sprintf('Response does not have the header `%s`', $header));
        $this->assertNotEquals($value, $actual, sprintf('Response header `%s` equals `%s`', $header, $value));
    }

    /**
     * Assert that response header contains a sub string
     *
     * @param string $header
     * @param string $value
     * @return void
     */
    protected function assertHeaderContains(string $header, string $value): void
    {
        $this->checkRequestWasHandled();

        $actual = $this->response->getHeaderLine($header);
        $this->assertNotEmpty($actual, sprintf('Response has header `%s`', $header));
        $this->assertStringContainsString($value, $actual, sprintf('Response header `%s` does not contain `%s`', $header, $value));
    }

    /**
     * Assert that response header contains a sub string
     *
     * @param string $header
     * @param string $value
     * @return void
     */
    protected function assertHeaderNotContains(string $header, string $value): void
    {
        $this->checkRequestWasHandled();

        $actual = $this->response->getHeaderLine($header);
        $this->assertNotEmpty($actual, sprintf('Response has header `%s`', $header));
        $this->assertStringNotContainsString($value, $actual, sprintf('Response header `%s` contains `%s`', $header, $value));
    }

    /**
     * Checks if the PHP session variable has a key set
     *
     * @param string $key
     * @return void
     */
    protected function assertSessionHas(string $key): void
    {
        $this->checkRequestWasHandled();
        $this->assertArrayHasKey($key, $_SESSION, sprintf('Session does not have the key `%s`', $key));
    }

    /**
     * Checks if the PHP session variable does not have a key set
     *
     * @param string $key
     * @return void
     */
    protected function assertSessionDoesNotHave(string $key): void
    {
        $this->checkRequestWasHandled();
        $this->assertArrayNotHasKey($key, $_SESSION, sprintf('Session has the key `%s`', $key));
    }

    /**
     * Checks if the PHP session variable equals the value provided
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    protected function assertSessionEquals(string $key, $value): void
    {
        $this->checkRequestWasHandled();
        $this->assertArrayHasKey($key, $_SESSION, sprintf('Session does not have the key `%s`', $key));
        $this->assertEquals($value, $_SESSION[$key], sprintf('Session key `%s` does not equal `%s`', $key, $value));
    }

    /**
     * Checks if the PHP session variable does not equal the value provided
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    protected function assertSessionNotEquals(string $key, $value): void
    {
        $this->checkRequestWasHandled();
        $this->assertArrayHasKey($key, $_SESSION, sprintf('Session does not have the key `%s`', $key));
        $this->assertNotEquals($value, $_SESSION[$key], sprintf('Session key `%s` equals `%s`', $key, $value));
    }

    /**
     * Assert that Cookie was set
     *
     * @param string $name
     * @return void
     */
    protected function assertCookieSet(string $name): void
    {
        $this->checkRequestWasHandled();
        $this->assertContains($name, array_keys($this->getResponseCookies()), sprintf('Cookie `%s` is not set', $name));
    }

    /**
     * Assert that a Cookie is not set
     *
     * @param string $name
     * @return void
     */

    protected function assertCookieNotSet(string $name): void
    {
        $this->checkRequestWasHandled();
        $this->assertNotContains($name, array_keys($this->getResponseCookies()), sprintf('Cookie `%s` is set', $name));
    }

    /**
     * Asserts that Cookie equals a value
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    protected function assertCookieEquals(string $name, string $value): void
    {
        $this->checkRequestWasHandled();
        $this->assertContains($name, array_keys($this->getResponseCookies()), sprintf('Cookie `%s` is not set', $name));
        $this->assertEquals($value, $this->getResponseCookies()[$name], sprintf('Cookie `%s` does not equal `%s`', $name, $value));
    }

    /**
    * Asserts that Cookie equals a value
    *
    * @param string $key
    * @param string $value
    * @return void
    */
    protected function assertCookieNotEquals(string $name, string $value): void
    {
        $this->checkRequestWasHandled();
        $this->assertContains($name, array_keys($this->getResponseCookies()), sprintf('Cookie `%s` is not set', $name));
        $this->assertNotEquals($value, $this->getResponseCookies()[$name], sprintf('Cookie `%s` equals `%s`', $name, $value));
    }

    /**
     * Internal method for getting cookies from the response
     *
     * @return array
     */
    private function getResponseCookies(): array
    {
        $result = [];
        if ($this->response) {
            foreach ($this->response->getHeader('Set-Cookie') as $cookie) {

                // Ignore expired cookies, aka deleting
                if (preg_match('/expires=([^;]*)/', $cookie, $matches) && strtotime($matches[1]) < time()) {
                    continue;
                }

                // parse cookie name and value
                preg_match('/^([^;]*)/', $cookie, $matches);
                list($name, $value) = explode('=', $matches[1]);

                $result[$name] = $value;
            }
        }

        return $result;
    }

    private function checkRequestWasHandled(): void
    {
        if (is_null($this->response)) {
            $this->fail('No response object, request was not handled');
        }
    }

    /**
     * Sets headers which will be added to the ServerRequestInterface request object
     *
     * @param array $headers ['PHP_AUTH_USER' => 'somebody@example.com']
     * @return self
     */
    protected function setHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Sets an array of Cookies which will be added to ServerRequestInterface request object
     *
     * @internal naming should be similar to PSR request language
     *
     * @param array $cookies key - value pairs
     * @return void
     */
    protected function setCookieParams(array $cookies): self
    {
        $this->cookies = $cookies;

        return $this;
    }

    /**
     * Sets the uploaded files which will be added to ServerRequestInterface request object
     *
     * @param array $files an array of UploadedFileInterface files
     * @return self
     */
    protected function setUploadedFiles(array $files): self
    {
        // Check file types
        foreach ($files as $file) {
            if (! $file instanceof UploadedFileInterface) {
                throw new InvalidArgumentException('setFiles takes an array of UploadedFileInterface objects');
            }
        }

        $this->files = $files; // preserve keys

        return $this;
    }

    /**
     * Sets the data which will be added to the TestSession
     *
     * @param array $session key values pairs e.g. ['token' => 1234];
     * @return self
     */
    protected function setSession(array $session): self
    {
        $this->session = $session;

        return $this;
    }

    /**
     * Sets the Server Params for the ServerRequestInterface request object which are normally taken from $_SERVER envrionment
     *
     * @param array $serverParams
     * @return self
     */
    protected function setServerParams(array $serverParams): self
    {
        $this->serverParams = $serverParams;

        return $this;
    }

    /**
     * Set $_ENV vars for testing (caution)
     *
     * @param array $env
     * @return self
     */
    protected function setEnvironment(array $env): self
    {
        $this->env = $env;

        return $this;
    }

    /**
     * Gets the Server Request
     *
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        if (! isset($this->serverRequest)) {
            throw new RuntimeException('Server request not set');
        }

        return $this->serverRequest;
    }

    /**
     * Undocumented function
     *
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        if (! isset($this->response)) {
            throw new RuntimeException('Response not set');
        }

        return $this->response;
    }
}
