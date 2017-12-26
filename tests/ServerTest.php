<?php

namespace UMA\RPC\Tests;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Pimple\Psr11\Container as Psr11Container;
use UMA\RPC\Server;

class ServerTest extends TestCase
{
    public function testServer()
    {
        $sut = new Server(new Psr11Container(new Container));

        self::assertSame(
            '{"jsonrpc":"2.0","error":{"code":-32700,"message":"Parse error"},"id":null}',
            $sut->run('}wut')
        );

        self::assertSame(
            '{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null}',
            $sut->run('true')
        );
    }
}
