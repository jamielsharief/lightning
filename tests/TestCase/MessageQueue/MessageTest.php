<?php declare(strict_types=1);

namespace Lightning\Test\TestCase\MessageQueue;

use PHPUnit\Framework\TestCase;
use Lightning\MessageQueue\Message;


class SendEmailNotification
{

}

final class MessageTest extends TestCase
{
    public function testGetBody(): void
    {
        $message = new SendEmailNotification();
        $this->assertEquals(
            $message,
            (new Message($message))->getObject()
        );
    }

    public function testGetId(): void
    {
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{32}/',
            (new Message(new SendEmailNotification()))->getId()
        );
    }

    public function testGetTimestamp(): void
    {
        $this->assertEquals(
            time(),
            (new Message(new SendEmailNotification()))->getTimestamp()
        );
    }

 
}
