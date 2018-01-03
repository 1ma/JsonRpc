<?php

declare(strict_types=1);

namespace UMA\RPC\Tests\Unit;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use UMA\RPC\Server;
use UMA\RPC\Tests\Fixture\Procedure\Subtractor;
use UMA\RPC\Tests\Fixture\Psr11\ArrayContainer;

class ServerTest extends TestCase
{
    public function testAddingANonExistentService()
    {
        $this->expectException(\LogicException::class);

        $sut = new Server(new ArrayContainer);
        $sut->add('subtract', Subtractor::class);
    }

    public function testInvalidProcedureService()
    {
        $container = new ArrayContainer([
            Subtractor::class => 'this is not a Procedure!'
        ]);

        $sut = new Server($container);
        $sut->add('subtract', Subtractor::class);

        self::assertSame(
            '{"jsonrpc":"2.0","error":{"code":-32603,"message":"Internal error"},"id":1}',
            $sut->run('{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}')
        );
    }

    public function testPsr11ContainerException()
    {
        /** @var MockObject|ArrayContainer $container */
        $container = $this->getMockBuilder(ArrayContainer::class)
            ->setConstructorArgs([[Subtractor::class => new Subtractor]])
            ->setMethods(['get'])
            ->getMock();

        $container->expects(self::once())
            ->method('get')
            ->with(Subtractor::class)
            ->will(self::throwException(new class extends \RuntimeException implements ContainerExceptionInterface {}));

        $sut = new Server($container);
        $sut->add('subtract', Subtractor::class);

        self::assertSame(
            '{"jsonrpc":"2.0","error":{"code":-32603,"message":"Internal error"},"id":1}',
            $sut->run('{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}')
        );
    }

    public function testInvalidParams()
    {
        $container = new ArrayContainer([
            Subtractor::class => new Subtractor
        ]);

        $sut = new Server($container);
        $sut->add('subtract', Subtractor::class);

        self::assertSame(
            '{"jsonrpc":"2.0","error":{"code":-32602,"message":"Invalid params"},"id":1}',
            $sut->run('{"jsonrpc": "2.0", "method": "subtract", "params": ["foo", "bar"], "id": 1}')
        );
    }
}
