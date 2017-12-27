<?php

declare(strict_types=1);

namespace UMA\RPC\Tests;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Pimple\Psr11\Container as Psr11Container;
use UMA\RPC\Procedure;
use UMA\RPC\Server;
use UMA\RPC\Tests\Fixture\Adder;
use UMA\RPC\Tests\Fixture\Subtractor;

class ServerTest extends TestCase
{
    /**
     * @var Server
     */
    private static $sut;

    public static function setUpBeforeClass()
    {
        $container = new Container();

        $container[Adder::class] = function(): Procedure {
            return new Adder();
        };

        $container[Subtractor::class] = function(): Procedure {
            return new Subtractor();
        };

        static::$sut = new Server(new Psr11Container($container));
        static::$sut
            ->add('sum', Adder::class)
            ->add('subtract', Subtractor::class);
    }

    public function testServer()
    {
        self::assertSame(
            '{"jsonrpc":"2.0","error":{"code":-32700,"message":"Parse error"},"id":null}',
            static::$sut->run('}wut')
        );

        self::assertSame(
            '{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null}',
            static::$sut->run('true')
        );

        self::assertSame(
            '{"jsonrpc":"2.0","result":19,"id":"1"}',
            static::$sut->run('{"jsonrpc": "2.0", "method": "subtract", "params": [42,23], "id": "1"}')
        );

        self::assertSame(
            '[{"jsonrpc":"2.0","result":19,"id":"1"},{"jsonrpc":"2.0","result":-19,"id":"2"}]',
            static::$sut->run('[{"jsonrpc": "2.0", "method": "subtract", "params": [42,23], "id": "1"},{"jsonrpc": "2.0", "method": "subtract", "params": [23,42], "id": "2"}]')
        );

        self::assertSame(
            '[{"jsonrpc":"2.0","result":6,"id":"2"}]',
            static::$sut->run('[{"jsonrpc": "2.0", "method": "subtract", "params": [42,23]},{"jsonrpc": "2.0", "method": "sum", "params": [1,2,3], "id": "2"}]')
        );

        self::assertNull(
            static::$sut->run('[{"jsonrpc": "2.0", "method": "subtract", "params": [42,23]},{"jsonrpc": "2.0", "method": "subtract", "params": [23,42]}]')
        );
    }
}
