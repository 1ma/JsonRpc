<?php

namespace UMA\RPC\Tests;

use PHPUnit\Framework\TestCase;
use UMA\RPC\Server;
use UMA\RPC\Tests\Fixtures\ArrayContainer;
use UMA\RPC\Tests\Fixtures\Subtraction;

/**
 * A test suite based on the examples found on section 7
 * of the JSON-RPC 2.0 specification.
 *
 * @see http://www.jsonrpc.org/specification#examples
 */
class ServerTest extends TestCase
{
    /**
     * @var Server
     */
    private static $sut;

    public static function setUpBeforeClass()
    {
        static::$sut = new Server(
            new ArrayContainer([
                Subtraction::class => new Subtraction
            ])
        );

        static::$sut->register('subtract', Subtraction::class);
    }

    public function testRpcCallWithInvalidJson()
    {
        self::assertSame(
            '{"jsonrpc":"2.0","error":{"code":-32700,"message":"Parse error"},"id":null}',
            static::$sut->process('{"jsonrpc":"2.0","method":"foobar,"params":"bar","baz]')
        );
    }

    /**
     * @dataProvider invalidRequestProvider
     */
    public function testRpcCallWithInvalidRequestObject(string $in)
    {
        self::assertSame(
            '{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null}',
            static::$sut->process($in)
        );
    }

    public function invalidRequestProvider(): array
    {
        return [
            'null' => ['null'],
            'boolean' => ['true'],
            'integer' => ['1'],
            'double' => ['1.2'],
            'string' => ['"foo"'],
            'empty string' => ['""'],
            'empty array' => ['[]'],
            'empty object' => ['{}'],
            'missing jsonrpc key' => ['{"method":"foo","params":[1,2]}'],
            'missing method key' => ['{"jsonrpc":"2.0","params":[]}'],
            'invalid version' => ['{"jsonrpc":"1.0","method":"foo","params":[1,2]}'],
            'invalid method' => ['{"jsonrpc":"2.0","method":["foo","bar"],"params":[1,2]}'],
            'invalid params' => ['{"jsonrpc":"2.0","method":"foo","params": 123}'],
            'invalid method and params' => ['{"jsonrpc":"2.0","method":1,"params":"bar"}'],
        ];
    }

    /**
     * @dataProvider invalidBatchProvider
     */
    public function testRpcCallWithInvalidBatch(string $in, int $amount)
    {
        $out = sprintf(
            '[%s]',
            implode(
                ',',
                array_fill(
                    0,
                    $amount,
                    '{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null}'
                )
            )
        );

        self::assertSame($out, static::$sut->process($in));
    }

    public function invalidBatchProvider(): array
    {
        return [
            'array of one integer' => ['[1]', 1],
            'array of three integers' => ['[1,2,3]', 3],
            'array of one empty object' => ['[{}]', 1]
        ];
    }
}
