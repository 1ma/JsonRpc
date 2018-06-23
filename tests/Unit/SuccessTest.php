<?php

declare(strict_types=1);

namespace UMA\JsonRpc\Tests\Unit;

use PHPUnit\Framework\TestCase;
use UMA\JsonRpc\Success;

class SuccessTest extends TestCase
{
    public function testSuccessfulResponsesSerialization(): void
    {
        self::assertSame(
            '{"jsonrpc":"2.0","result":19,"id":"1"}',
            \json_encode(new Success('1', 19))
        );

        self::assertSame(
            '{"jsonrpc":"2.0","result":null,"id":1}',
            \json_encode(new Success(1))
        );

        self::assertSame(
            '{"jsonrpc":"2.0","result":null,"id":null}',
            \json_encode(new Success(null))
        );

        self::assertSame(
            '[{"jsonrpc":"2.0","result":19,"id":"1"},{"jsonrpc":"2.0","result":42,"id":"2"}]',
            \json_encode([new Success('1', 19), new Success('2', 42)])
        );
    }
}
