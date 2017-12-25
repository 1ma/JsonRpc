<?php

declare(strict_types=1);

namespace UMA\RPC\Tests;

use PHPUnit\Framework\TestCase;
use UMA\RPC\Payload;

class PayloadTest extends TestCase
{
    public function testPayloadParseError()
    {
        $sut = new Payload('}"jsonrpc":"2.0');

        self::assertFalse($sut->parsable());
        self::assertNull($sut->decoded());

        self::assertFalse($sut->isSingle());
        self::assertFalse($sut->isBatch());
    }

    /**
     * @dataProvider validPayloadsProvider
     */
    public function testValidPayloads(string $in)
    {
        $sut = new Payload($in);

        self::assertTrue($sut->parsable());
        self::assertEquals($sut->decoded(), \json_decode($in));

        self::assertTrue($sut->isSingle() || $sut->isBatch());
    }

    public function validPayloadsProvider(): array
    {
        return [
            'empty object' => ['{}'],
            'derpy object' => ['{"foo":123}'],
            'non empty array' => ['["wut"]'],
            'valid request' => ['{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}'],
            'valid batch request' => ['[{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}]']
        ];
    }

    /**
     * @dataProvider invalidPayloadsProvider
     */
    public function testInvalidPayloads(string $in)
    {
        $sut = new Payload($in);

        self::assertTrue($sut->parsable());
        self::assertEquals($sut->decoded(), \json_decode($in));

        self::assertFalse($sut->isSingle());
        self::assertFalse($sut->isBatch());
    }

    public function invalidPayloadsProvider(): array
    {
        return [
            'null' => ['null'],
            'boolean' => ['true'],
            'integer' => ['1'],
            'double' => ['1.2'],
            'string' => ['"foo"'],
            'empty string' => ['""'],
            'empty array' => ['[]']
        ];
    }
}
