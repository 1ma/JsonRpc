<?php

declare(strict_types=1);

namespace UMA\RPC\Tests;

use PHPUnit\Framework\TestCase;
use UMA\RPC\Error;

class ErrorTest extends TestCase
{
    public function testErrorResponses()
    {
        self::assertSame(
            '{"jsonrpc":"2.0","error":{"code":-32700,"message":"Parse error"},"id":null}',
            (string) Error::parsing()
        );

        self::assertSame(
            '{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null}',
            (string) Error::invalidRequest()
        );

        self::assertSame(
            '{"jsonrpc":"2.0","error":{"code":-32601,"message":"Method not found"},"id":"123"}',
            (string) Error::unknownMethod('123')
        );

        self::assertSame(
            '{"jsonrpc":"2.0","error":{"code":-32602,"message":"Invalid params"},"id":123}',
            (string) Error::invalidParams(123)
        );

        self::assertSame(
            '{"jsonrpc":"2.0","error":{"code":-32603,"message":"Internal error"},"id":"abc"}',
            (string) Error::internal('abc')
        );

        self::assertSame(
            '{"jsonrpc":"2.0","error":{"code":-32000,"message":"Server error","data":"Division by zero"},"id":1}',
            (string) Error::userDefined(1, 'Division by zero')
        );
    }
}
