<?php

declare(strict_types=1);

namespace UMA\JsonRpc\Tests\Unit;

use PHPUnit\Framework\TestCase;
use UMA\JsonRpc\Error;

class ErrorTest extends TestCase
{
    public function testErrorResponses(): void
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
            '{"jsonrpc":"2.0","error":{"code":-123,"message":"Division by zero"},"id":null}',
            \json_encode(new Error(-123, 'Division by zero'))
        );

        self::assertSame(
            '{"jsonrpc":"2.0","error":{"code":-456,"message":"Unknown user","data":{"email":"john.doe@example.com"}},"id":999}',
            \json_encode(new Error(-456, 'Unknown user', ['email' => 'john.doe@example.com'], 999))
        );

        self::assertSame(
            '[{"jsonrpc":"2.0","error":{"code":-32700,"message":"Parse error"},"id":null},{"jsonrpc":"2.0","error":{"code":-32601,"message":"Method not found"},"id":"123"}]',
            \json_encode([Error::parsing(), Error::unknownMethod('123')])
        );
    }
}
