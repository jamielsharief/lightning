<?php declare(strict_types=1);

namespace Lightning\Test\ServiceObject;

use Lightning\Params\Params;
use PHPUnit\Framework\TestCase;
use Lightning\ServiceObject\Result;
use Lightning\ServiceObject\AbstractServiceObject;

class ServiceObject extends AbstractServiceObject
{
    private bool $initialized = false;

    protected function initialize(): void
    {
        parent::initialize();
        $this->initialized = true;
    }
    public function execute(Params $params): Result
    {
        return new Result(true, [
            'params' => $params,
            'initialized' => $this->initialized
        ]);
    }
}

final class AbstractServiceObjectTest extends TestCase
{
    public function testDispatch()
    {
        $service = new ServiceObject();

        $result = $service->run();
        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->get('initialized'));
    }

    public function testDispatchWithParameters()
    {
        $service = new ServiceObject();

        $this->assertEquals('bar', $service->withParameters(['foo' => 'bar'])
            ->run()
            ->get('params')
            ->get('foo'));
    }

    public function testGetParameters(): void
    {
        $service = new ServiceObject();
        $this->assertEquals([], $service->getParameters());
        $this->assertEquals(['foo' => 'bar'], $service->withParameters(['foo' => 'bar'])->getParameters());
    }

    public function testDispatchWithParametersArray()
    {
        $service = new ServiceObject();

        $this->assertEquals('bar', $service->withParameters(['foo' => 'bar'])
            ->run()
            ->get('params')
            ->get('foo'));
    }

    public function testIsInvokable(): void
    {
        $service = (new ServiceObject())->withParameters(['foo' => 'bar']);

        $this->assertIsCallable($service);
        $this->assertInstanceOf(Result::class, $service());
    }
}
