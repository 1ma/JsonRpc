<?php

declare(strict_types=1);

namespace UMA\JsonRpc\Tests\Unit;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use UMA\DIC\Container;
use UMA\JsonRpc\Server;
use UMA\JsonRpc\Tests\Fixture\Subtractor;

class ServerTest extends TestCase
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var Server
     */
    private $sut;

    protected function setUp()
    {
        $this->container = new Container;
        $this->sut = new Server($this->container);
    }

    public function testAddingANonExistentService()
    {
        $this->expectException(\LogicException::class);

        $this->sut->add('subtract', Subtractor::class);
    }

    public function testInvalidProcedureService()
    {
        $this->container->set(Subtractor::class, 'this is not a Procedure!');

        $this->sut->add('subtract', Subtractor::class);

        self::assertSame(
            '{"jsonrpc":"2.0","error":{"code":-32603,"message":"Internal error"},"id":1}',
            $this->sut->run('{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}')
        );
    }

    public function testInvalidParams()
    {
        $this->container->set(Subtractor::class, new Subtractor);

        $this->sut->add('subtract', Subtractor::class);

        self::assertSame(
            '{"jsonrpc":"2.0","error":{"code":-32602,"message":"Invalid params"},"id":1}',
            $this->sut->run('{"jsonrpc": "2.0", "method": "subtract", "params": ["foo", "bar"], "id": 1}')
        );
    }

    public function testPsr11ContainerException()
    {
        /** @var MockObject|Container $container */
        $container = $this->getMockBuilder(Container::class)
            ->setMethods(['get'])
            ->getMock();

        $container->expects(self::once())
            ->method('get')
            ->with(Subtractor::class)
            ->will(self::throwException(new class extends \RuntimeException implements ContainerExceptionInterface {}));

        $container->set(Subtractor::class, new Subtractor);

        $sut = new Server($container);
        $sut->add('subtract', Subtractor::class);

        self::assertSame(
            '{"jsonrpc":"2.0","error":{"code":-32603,"message":"Internal error"},"id":1}',
            $sut->run('{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}')
        );
    }
}
