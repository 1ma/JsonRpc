<?php

declare(strict_types=1);

namespace UMA\JsonRpc\Tests\Unit;

use PHPUnit\Framework\TestCase;
use UMA\DIC\Container;
use UMA\JsonRpc\ConcurrentServer;
use UMA\JsonRpc\Tests\Fixture\SlowProcedure;
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

    public function testConcurrencyActuallyWorks(): void
    {
        $container = new Container([
            SlowProcedure::class => new SlowProcedure
        ]);

        $server = new ConcurrentServer($container);
        $server->set('slow', SlowProcedure::class);

        $time = (int)(microtime(true) * 10**6);
        $response = $server->run('[
          {"jsonrpc": "2.0", "method": "slow", "params": {"wait_time": 1}, "id": "0"},
          {"jsonrpc": "2.0", "method": "slow", "params": {"wait_time": 1}, "id": "1"},
          {"jsonrpc": "2.0", "method": "slow", "params": {"wait_time": 1}, "id": "2"},
          {"jsonrpc": "2.0", "method": "slow", "params": {"wait_time": 1}, "id": "3"},
          {"jsonrpc": "2.0", "method": "slow", "params": {"wait_time": 1}, "id": "4"},
          {"jsonrpc": "2.0", "method": "slow", "params": {"wait_time": 1}, "id": "5"},
          {"jsonrpc": "2.0", "method": "slow", "params": {"wait_time": 1}, "id": "6"},
          {"jsonrpc": "2.0", "method": "slow", "params": {"wait_time": 1}, "id": "7"},
          {"jsonrpc": "2.0", "method": "slow", "params": {"wait_time": 1}, "id": "8"},
          {"jsonrpc": "2.0", "method": "slow", "params": {"wait_time": 1}, "id": "9"}
        ]');
        $time = (int)(microtime(true) * 10**6) - $time;

        // The server has to take less than 1.5s to process
        // all 10 requests instead of the usual 10s.
        self::assertTrue($time < 1500000);

        self::assertSame(
            '[{"jsonrpc":"2.0","result":"0","id":"0"},{"jsonrpc":"2.0","result":"1","id":"1"},{"jsonrpc":"2.0","result":"2","id":"2"},{"jsonrpc":"2.0","result":"3","id":"3"},{"jsonrpc":"2.0","result":"4","id":"4"},{"jsonrpc":"2.0","result":"5","id":"5"},{"jsonrpc":"2.0","result":"6","id":"6"},{"jsonrpc":"2.0","result":"7","id":"7"},{"jsonrpc":"2.0","result":"8","id":"8"},{"jsonrpc":"2.0","result":"9","id":"9"}]',
            $response
        );
    }
}
