<?php declare(strict_types=1);

namespace Lightning\Test\Worker;

use Lightning\Params\Params;
use PHPUnit\Framework\TestCase;
use Lightning\Worker\AbstractJob;

class SendEmailNotification extends AbstractJob
{
    protected int $maxRetries = 3;
    protected int $delay = 30;

    public function __construct()
    {
    }

    protected function initialize(): void
    {
    }

    protected function execute(Params $params): void
    {
        if ($params->has('testCase')) {
            $testCase = $params->get('testCase');
            $testCase->assertNotNull($testCase);
        }
    }
}

final class AbstractJobTest extends TestCase
{
    public function testRun(): void
    {
        $job = new SendEmailNotification();
        $result = $job->run();
        $this->assertNull($result);
    }

    public function testAttempts(): void
    {
        $job = new SendEmailNotification();
        $this->assertEquals(0, $job->attempts());
        $job->fail();
        $this->assertEquals(1, $job->attempts());
        $job->fail();
        $this->assertEquals(2, $job->attempts());
    }

    public function testWithParams(): void
    {
        $job = new SendEmailNotification();
        $this->assertEquals(['foo' => 'bar'], $job->withParameters(['foo' => 'bar'])->getParameters());
    }

    public function testUsedParams(): void
    {
        $job = new SendEmailNotification();
        $job->withParameters(['testCase' => $this])->run();
    }

    public function testMaxRetries(): void
    {
        $job = new SendEmailNotification();
        $this->assertEquals(3, $job->maxRetries());
    }

    public function testDelay(): void
    {
        $job = new SendEmailNotification();
        $this->assertEquals(30, $job->delay());
    }
}
