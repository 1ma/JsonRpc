<?php

declare(strict_types=1);

namespace UMA\JsonRpc\Tests\Unit;

use PHPUnit\Framework\TestCase;
use UMA\DIC\Container;
use UMA\JsonRpc\ConcurrentServer;
use UMA\JsonRpc\Tests\Fixture\Subtractor;

class ConcurrentServerTest extends TestCase
{
    public function testTooManyBatchRequestsSent(): void
    {
        $container = new Container([
            Subtractor::class => new Subtractor
        ]);

        $limitedServer = new ConcurrentServer($container, 1);
        $limitedServer->set('subtract', Subtractor::class);

        self::assertSame(
            '{"jsonrpc":"2.0","error":{"code":-32000,"message":"Too many batch requests sent to server","data":{"limit":1}},"id":null}',
            $limitedServer->run('[
              {"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1},
              {"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 2}
            ]')
        );
    }
}
