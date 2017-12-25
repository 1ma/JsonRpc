<?php

declare(strict_types=1);

namespace UMA\RPC\Tests;

use PHPUnit\Framework\TestCase;
use UMA\RPC\Success;

class SuccessTest extends TestCase
{
    public function testErrorResponses()
    {
        self::assertSame(
            '{"jsonrpc":"2.0","result":19,"id":"1"}',
            (string) new Success('1', 19)
        );
    }
}
