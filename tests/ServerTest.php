<?php

declare(strict_types=1);

namespace UMA\RPC\Tests;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Pimple\Psr11\Container as Psr11Decorator;
use UMA\RPC\Server;
use UMA\RPC\Tests\Fixture\Adder;
use UMA\RPC\Tests\Fixture\Subtractor;

class ServerTest extends TestCase
{
    public function testAddingANonExistentService()
    {
        $this->expectException(\LogicException::class);

        $sut = new Server(new Psr11Decorator(new Container));
        $sut->add('sum', Adder::class);
    }

    public function testInvalidProcedure()
    {
        $container = new Container;

        $container[Subtractor::class] = function () {
            return null;
        };

        $sut = new Server(new Psr11Decorator($container));
        $sut->add('subtract', Subtractor::class);

        self::assertSame(
            '{"jsonrpc":"2.0","error":{"code":-32603,"message":"Internal error"},"id":1}',
            $sut->run('{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}')
        );
    }

    public function testInvalidParams()
    {
        $container = new Container;

        $container[Subtractor::class] = function () {
            return new Subtractor;
        };

        $sut = new Server(new Psr11Decorator($container));
        $sut->add('subtract', Subtractor::class);

        self::assertSame(
            '{"jsonrpc":"2.0","error":{"code":-32602,"message":"Invalid params"},"id":1}',
            $sut->run('{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23, 12], "id": 1}')
        );
    }
}
