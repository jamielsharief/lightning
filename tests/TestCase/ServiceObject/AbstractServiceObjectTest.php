<?php declare(strict_types=1);

namespace Lightning\Test\ServiceObject;

use PHPUnit\Framework\TestCase;
use Lightning\ServiceObject\Params;
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

    public function testDispatchWithParams()
    {
        $service = new ServiceObject();

        $this->assertEquals('bar', $service->withParams(['foo' => 'bar'])
            ->run()
            ->get('params')
            ->get('foo'));
    }

    public function testGetParams(): void
    {
        $service = new ServiceObject();
        $this->assertEquals([], $service->getParams());
        $this->assertEquals(['foo' => 'bar'], $service->withParams(['foo' => 'bar'])->getParams());
    }

    public function testDispatchWithParamsArray()
    {
        $service = new ServiceObject();

        $this->assertEquals('bar', $service->withParams(['foo' => 'bar'])
            ->run()
            ->get('params')
            ->get('foo'));
    }

    public function testIsInvokable(): void
    {
        $params = new Params(['foo' => 'bar']);
        $service = (new ServiceObject())->withParams(['foo' => 'bar']);

        $this->assertIsCallable($service);
        $this->assertInstanceOf(Result::class, $service());
    }
}
