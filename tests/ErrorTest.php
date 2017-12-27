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
            \json_encode(Error::parsing())
        );

        self::assertSame(
            '{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null}',
            \json_encode(Error::invalidRequest())
        );

        self::assertSame(
            '{"jsonrpc":"2.0","error":{"code":-32601,"message":"Method not found"},"id":"123"}',
            \json_encode(Error::unknownMethod('123'))
        );

        self::assertSame(
            '{"jsonrpc":"2.0","error":{"code":-32602,"message":"Invalid params"},"id":123}',
            \json_encode(Error::invalidParams(123))
        );

        self::assertSame(
            '{"jsonrpc":"2.0","error":{"code":-32603,"message":"Internal error"},"id":"abc"}',
            \json_encode(Error::internal('abc'))
        );

        self::assertSame(
            '{"jsonrpc":"2.0","error":{"code":-32000,"message":"Server error","data":"Division by zero"},"id":1}',
            \json_encode(Error::custom(1, 'Division by zero'))
        );

        self::assertSame(
            '[{"jsonrpc":"2.0","error":{"code":-32700,"message":"Parse error"},"id":null},{"jsonrpc":"2.0","error":{"code":-32601,"message":"Method not found"},"id":"123"}]',
            \json_encode([Error::parsing(), Error::unknownMethod('123')])
        );
    }
}
