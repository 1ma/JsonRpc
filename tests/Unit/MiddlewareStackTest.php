<?php

declare(strict_types=1);

namespace UMA\JsonRpc\Tests\Unit;

use PHPUnit\Framework\TestCase;
use stdClass;
use UMA\JsonRpc\Internal\Input;
use UMA\JsonRpc\Internal\MiddlewareStack;
use UMA\JsonRpc\Request;
use UMA\JsonRpc\Tests\Fixture;

final class MiddlewareStackTest extends TestCase
{
    public function testStackWithNoMiddlewaresIsJustTheInnermostProcedure(): void
    {
        $stack = MiddlewareStack::compose($procedure = new Fixture\Adder());

        self::assertSame($procedure, $stack);
        self::assertNotNull($stack->getSpec());
    }

    public function testNormalUsage(): void
    {
        $stack = MiddlewareStack::compose($procedure = new Fixture\Adder(), $middleware = new Fixture\LoggingMiddleware());

        self::assertNotSame($procedure, $stack);
        self::assertNull($stack->getSpec());

        $payload = new stdClass();
        $payload->jsonrpc = '2.0';
        $payload->method = 'adder';
        $payload->params = [1, 2, 3];
        $payload->id = '1';

        $response = $stack(new Request(Input::fromSafeData($payload)));

        self::assertSame([
            'jsonrpc' => '2.0',
            'result' => 6,
            'id' => '1',
        ], $response->jsonSerialize());

        self::assertNotEmpty($middleware->getSeenResponses());
        self::assertSame('{"jsonrpc":"2.0","result":6,"id":"1"}', $middleware->getSeenResponses()[0]);
    }
}
