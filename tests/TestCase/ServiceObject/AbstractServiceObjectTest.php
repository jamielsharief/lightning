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
    public function execute(Params $params, Result $result): Result
    {
        return $result->withData([
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

        $params = new Params(['foo' => 'bar']);
        $this->assertEquals('bar', $service->withParams($params)
            ->run()
            ->get('params')
            ->get('foo'));
    }

    public function testIsInvokable(): void
    {
        $params = new Params(['foo' => 'bar']);
        $service = (new ServiceObject())->withParams($params);

        $this->assertIsCallable($service);
        $this->assertInstanceOf(Result::class, $service());
    }
}
