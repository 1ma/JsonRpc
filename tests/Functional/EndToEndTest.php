<?php

declare(strict_types=1);

namespace UMA\JsonRpc\Tests\Functional;

use PHPUnit\Framework\TestCase;
use UMA\DIC\Container;
use UMA\JsonRpc\ConcurrentServer;
use UMA\JsonRpc\Server;
use UMA\JsonRpc\Tests\Fixture\Adder;
use UMA\JsonRpc\Tests\Fixture\SlowProcedure;
use UMA\JsonRpc\Tests\Fixture\Subtractor;
use UMA\JsonRpc\Tests\Fixture\MockProcedure;

class EndToEndTest extends TestCase
{
    /**
     * @dataProvider specExamplesProvider
     */
    public function testRegularServer(string $input, ?string $expected): void
    {
        $container = new Container([
            Adder::class => new Adder(),
            Subtractor::class => new Subtractor(),
            MockProcedure::class => new MockProcedure()
        ]);

        $sut = (new Server($container))
            ->set('get_data', MockProcedure::class)
            ->set('notify_hello', MockProcedure::class)
            ->set('sum', Adder::class)
            ->set('subtract', Subtractor::class)
            ->set('update', MockProcedure::class);

        self::assertSame($expected, $sut->run($input));
    }

    /**
     * @dataProvider specExamplesProvider
     */
    public function testConcurrentServer(string $input, ?string $expected): void
    {
        // This is the 'rpc call Batch' test
        if (366 === \strlen($input)) {
            self::markTestSkipped('For batch requests ConcurrentServer does not guarantee the order of each response');
        }

        $container = new Container([
            Adder::class => new Adder(),
            Subtractor::class => new Subtractor(),
            MockProcedure::class => new MockProcedure()
        ]);

        $sut = (new ConcurrentServer($container))
            ->set('get_data', MockProcedure::class)
            ->set('notify_hello', MockProcedure::class)
            ->set('sum', Adder::class)
            ->set('subtract', Subtractor::class)
            ->set('update', MockProcedure::class);

        self::assertSame($expected, $sut->run($input));
    }

    /**
     * This provider lists all the practical examples found
     * on the official JSON-RPC 2.0 spec document.
     *
     * @see http://www.jsonrpc.org/specification#examples
     */
    public function specExamplesProvider(): array
    {
        return [
            'rpc call with positional parameters #1' => [
                '{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}',
                '{"jsonrpc":"2.0","result":19,"id":1}'
            ],

            'rpc call with positional parameters #2' => [
                '{"jsonrpc": "2.0", "method": "subtract", "params": [23, 42], "id": 2}',
                '{"jsonrpc":"2.0","result":-19,"id":2}'
            ],

            'rpc call with named parameters #1' => [
                '{"jsonrpc": "2.0", "method": "subtract", "params": {"subtrahend": 23, "minuend": 42}, "id": 3}',
                '{"jsonrpc":"2.0","result":19,"id":3}'
            ],

            'rpc call with named parameters #2' => [
                '{"jsonrpc": "2.0", "method": "subtract", "params": {"minuend": 42, "subtrahend": 23}, "id": 4}',
                '{"jsonrpc":"2.0","result":19,"id":4}'
            ],

            'a Notification' => [
                '{"jsonrpc": "2.0", "method": "update", "params": [1,2,3,4,5]}',
                null
            ],

            'rpc call on non-existent method' => [
                '{"jsonrpc": "2.0", "method": "foobar", "id": "1"}',
                '{"jsonrpc":"2.0","error":{"code":-32601,"message":"Method not found"},"id":"1"}'
            ],

            'rpc call with invalid JSON' => [
                '{"jsonrpc": "2.0", "method": "foobar, "params": "bar", "baz]',
                '{"jsonrpc":"2.0","error":{"code":-32700,"message":"Parse error"},"id":null}'
            ],

            'rpc call with invalid Request object' => [
                '{"jsonrpc": "2.0", "method": 1, "params": "bar"}',
                '{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null}'
            ],

            'rpc call Batch, invalid JSON' => [
                '[
  {"jsonrpc": "2.0", "method": "sum", "params": [1,2,4], "id": "1"},
  {"jsonrpc": "2.0", "method"
]',
                '{"jsonrpc":"2.0","error":{"code":-32700,"message":"Parse error"},"id":null}'
            ],

            'rpc call with an empty Array' => [
                '[]',
                '{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null}'
            ],

            'rpc call with an invalid Batch (but not empty)' => [
                '[1]',
                '[{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null}]'
            ],

            'rpc call with invalid Batch' => [
                '[1,2,3]',
                '[{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null},{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null},{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null}]'
            ],

            'rpc call Batch' => [
                '[
  {"jsonrpc": "2.0", "method": "sum", "params": [1,2,4], "id": "1"},
  {"jsonrpc": "2.0", "method": "notify_hello", "params": [7]},
  {"jsonrpc": "2.0", "method": "subtract", "params": [42,23], "id": "2"},
  {"foo": "boo"},
  {"jsonrpc": "2.0", "method": "foo.get", "params": {"name": "myself"}, "id": "5"},
  {"jsonrpc": "2.0", "method": "get_data", "id": "9"} 
]',
                '[{"jsonrpc":"2.0","result":7,"id":"1"},{"jsonrpc":"2.0","result":19,"id":"2"},{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null},{"jsonrpc":"2.0","error":{"code":-32601,"message":"Method not found"},"id":"5"},{"jsonrpc":"2.0","result":["hello",5],"id":"9"}]'
            ],

            'rpc call Batch (all notifications)' => [
                '[
  {"jsonrpc": "2.0", "method": "notify_sum", "params": [1,2,4]},
  {"jsonrpc": "2.0", "method": "notify_hello", "params": [7]}
]',
                null
            ]
        ];
    }

    public function testConcurrencyActuallyWorks(): void
    {
        $container = new Container([
            SlowProcedure::class => new SlowProcedure()
        ]);

        $server = new ConcurrentServer($container);
        $server->set('slow', SlowProcedure::class);

        $time = (int)(microtime(true) * 10**6);
        $response = $server->run('[
          {"jsonrpc": "2.0", "method": "slow", "params": {"wait_time": 500000}, "id": "0"},
          {"jsonrpc": "2.0", "method": "slow", "params": {"wait_time": 400000}, "id": "1"},
          {"jsonrpc": "2.0", "method": "slow", "params": {"wait_time": 300000}, "id": "2"},
          {"jsonrpc": "2.0", "method": "slow", "params": {"wait_time": 200000}, "id": "3"},
          {"jsonrpc": "2.0", "method": "slow", "params": {"wait_time": 100000}, "id": "4"}
        ]');
        $time = (int)(microtime(true) * 10**6) - $time;

        // The server has to take less than 0.6s to process
        // all 5 requests instead of the usual 1.5s.
        self::assertLessThan(600000, $time);

        // Since the ConcurrentServer writes the result of each individual request
        // as soon as it is available, the final response has to be in reverse order
        // in this particular test run.
        self::assertSame(
            '[{"jsonrpc":"2.0","result":"4","id":"4"},{"jsonrpc":"2.0","result":"3","id":"3"},{"jsonrpc":"2.0","result":"2","id":"2"},{"jsonrpc":"2.0","result":"1","id":"1"},{"jsonrpc":"2.0","result":"0","id":"0"}]',
            $response
        );
    }
}
