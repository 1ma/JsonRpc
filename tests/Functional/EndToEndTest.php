<?php

declare(strict_types=1);

namespace UMA\JsonRpc\Tests\Functional;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Pimple\Psr11\Container as Psr11Decorator;
use UMA\JsonRpc\Procedure;
use UMA\JsonRpc\Server;
use UMA\JsonRpc\Tests\Fixture\Procedure\Adder;
use UMA\JsonRpc\Tests\Fixture\Procedure\Subtractor;
use UMA\JsonRpc\Tests\Fixture\Procedure\MockProcedure;

class EndToEndTest extends TestCase
{
    /**
     * @var Server
     */
    private $sut;

    protected function setUp()
    {
        $container = new Container();

        $container[Adder::class] = function(): Procedure {
            return new Adder();
        };

        $container[Subtractor::class] = function(): Procedure {
            return new Subtractor();
        };

        $container[MockProcedure::class] = function(): Procedure {
            return new MockProcedure();
        };

        $this->sut = new Server(new Psr11Decorator($container));
        $this->sut
            ->add('get_data', MockProcedure::class)
            ->add('notify_hello', MockProcedure::class)
            ->add('sum', Adder::class)
            ->add('subtract', Subtractor::class)
            ->add('update', MockProcedure::class);
    }

    /**
     * @dataProvider specExamplesProvider
     */
    public function testServer(string $input, ?string $expected)
    {
        self::assertSame($expected, $this->sut->run($input));
    }

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
