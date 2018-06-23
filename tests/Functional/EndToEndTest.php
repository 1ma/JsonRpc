<?php

declare(strict_types=1);

namespace UMA\JsonRpc\Tests\Functional;

use PHPUnit\Framework\TestCase;
use UMA\DIC\Container;
use UMA\JsonRpc\Server;
use UMA\JsonRpc\Tests\Fixture\Adder;
use UMA\JsonRpc\Tests\Fixture\Subtractor;
use UMA\JsonRpc\Tests\Fixture\MockProcedure;

class EndToEndTest extends TestCase
{
    /**
     * @dataProvider specExamplesProvider
     */
    public function testFullOrchestra(string $input, ?string $expected): void
    {
        $container = new Container([
            Adder::class => new Adder(),
            Subtractor::class => new Subtractor(),
            MockProcedure::class => new MockProcedure()
        ]);

        $sut = (new Server($container))
            ->add('get_data', MockProcedure::class)
            ->add('notify_hello', MockProcedure::class)
            ->add('sum', Adder::class)
            ->add('subtract', Subtractor::class)
            ->add('update', MockProcedure::class);

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
}
