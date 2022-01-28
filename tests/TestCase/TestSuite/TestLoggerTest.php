<?php declare(strict_types=1);

namespace Lightning\Test\TestSuite;

use Psr\Log\LogLevel;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Lightning\TestSuite\TestLogger;

final class TestLoggerTest extends TestCase
{
    public function testLogException(): void
    {
        $logger = new TestLogger();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unkown log level `foo`');

        $logger->log('foo', 'What happened');
    }

    public function testHasMessage(): void
    {
        $logger = new TestLogger();
        $this->assertFalse($logger->hasMessage('An error occured when requesting {url}', LogLevel::ERROR));

        $logger->log(LogLevel::ERROR, 'An error occured when requesting {url}', ['url' => 'http://localhost']);

        $this->assertTrue($logger->hasMessage('An error occured when requesting http://localhost', LogLevel::ERROR));
        $this->assertTrue($logger->hasMessage('An error occured when requesting {url}', LogLevel::ERROR, false));
    }

    public function testHasMessageThatContains(): void
    {
        $logger = new TestLogger();
        $this->assertFalse($logger->hasMessageThatContains('An error occured when requesting {url}', LogLevel::ERROR));

        $logger->log(LogLevel::ERROR, 'error occured when requesting {url}', ['url' => 'http://localhost']);

        $this->assertTrue($logger->hasMessageThatContains('error occured when requesting http://localhost', LogLevel::ERROR));
        $this->assertTrue($logger->hasMessageThatContains('error occured when requesting {url}', LogLevel::ERROR, false));
    }

    public function testHasMessageThatMatches(): void
    {
        $logger = new TestLogger();
        $this->assertFalse($logger->hasMessageThatMatches('/an error occured/i', LogLevel::ERROR));

        $logger->log(LogLevel::ERROR, 'An error occured when requesting {url}', ['url' => 'http://localhost']);

        $this->assertTrue($logger->hasMessageThatMatches('/an error occured/i', LogLevel::ERROR));
        $this->assertTrue($logger->hasMessageThatMatches('/an error occured/i', LogLevel::ERROR, false));
    }

    public function testFilter(): void
    {
        $logger = new TestLogger();
        $this->assertEquals([], $logger->filter(function (array $message) {
            return $message['level'] === LogLevel::ERROR;
        }));

        $logger->log(LogLevel::ERROR, 'An error occured when requesting {url}', ['url' => 'http://localhost']);

        $expected = [
            [
                'level' => 'error',
                'message' => 'An error occured when requesting {url}',
                'context' => [
                    'url' => 'http://localhost'
                ],
                'rendered' => 'An error occured when requesting http://localhost'
            ]
        ];
        $this->assertSame($expected, $logger->filter(function (array $message) {
            return $message['level'] === LogLevel::ERROR;
        }));

        $logger->log(LogLevel::ERROR, 'Error loading record #{id}', ['id' => 12345]);

        $expected[] = [
            'level' => 'error',
            'message' => 'Error loading record #{id}',
            'context' => [
                'id' => 12345
            ],
            'rendered' => 'Error loading record #12345'
        ];
        $this->assertSame($expected, $logger->filter(function (array $message) {
            return $message['level'] === LogLevel::ERROR;
        }));
    }

    public function testGetMessages(): void
    {
        $logger = new TestLogger();
        $this->assertEquals([], $logger->getMessages(LogLevel::ERROR));

        $logger->log(LogLevel::ERROR, 'An error occured when requesting {url}', ['url' => 'http://localhost']);

        $expected = [
            [
                'level' => 'error',
                'message' => 'An error occured when requesting {url}',
                'context' => [
                    'url' => 'http://localhost'
                ],
                'rendered' => 'An error occured when requesting http://localhost'
            ]
        ];
        $this->assertSame($expected, $logger->getMessages());

        $logger->log(LogLevel::DEBUG, 'Loading record #{id}', ['id' => 12345]);

        $expected[] = [
            'level' => 'debug',
            'message' => 'Loading record #{id}',
            'context' => [
                'id' => 12345
            ],
            'rendered' => 'Loading record #12345'
        ];
        $this->assertSame($expected, $logger->getMessages());
    }

    public function testGetMessagesByLevel(): void
    {
        $logger = new TestLogger();
        $this->assertEquals([], $logger->getMessages(LogLevel::ERROR));

        $logger->log(LogLevel::DEBUG, 'Requesting {url}', ['url' => 'http://localhost']);
        $logger->log(LogLevel::ERROR, 'An error occured when requesting {url}', ['url' => 'http://localhost']);

        $expected = [
            [
                'level' => 'error',
                'message' => 'An error occured when requesting {url}',
                'context' => [
                    'url' => 'http://localhost'
                ],
                'rendered' => 'An error occured when requesting http://localhost'
            ]
        ];
        $this->assertSame($expected, $logger->getMessages(LogLevel::ERROR));
    }

    public function testCount(): void
    {
        $logger = new TestLogger();
        $this->assertCount(0, $logger);

        $logger->log(LogLevel::ERROR, 'Test');
        $this->assertCount(1, $logger);

        $logger->log(LogLevel::ERROR, 'Test');
        $this->assertCount(2, $logger);
    }

    public function testReset(): void
    {
        $logger = $empty = new TestLogger();
        $logger->log(LogLevel::ERROR, 'Test');
        $this->assertCount(1, $logger);

        $logger->reset();

        $this->assertCount(0, $logger);
    }
}
