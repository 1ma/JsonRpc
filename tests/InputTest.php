<?php

declare(strict_types=1);

namespace UMA\RPC\Tests;

use PHPUnit\Framework\TestCase;
use UMA\RPC\Input;

class InputTest extends TestCase
{
    /**
     * @dataProvider validInputsProvider
     */
    public function testValidInputs(string $in)
    {
        $sut = new Input($in);

        self::assertTrue($sut->parsable());
        self::assertEquals($sut->decoded(), \json_decode($in));

        self::assertTrue($sut->isSingle() || $sut->isBatch());
    }

    public function validInputsProvider(): array
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
     * @dataProvider parseErrorProvider
     */
    public function testInputParseError(string $raw)
    {
        $sut = new Input($raw);

        self::assertFalse($sut->parsable());
        self::assertNull($sut->decoded());

        self::assertFalse($sut->isSingle());
        self::assertFalse($sut->isBatch());
    }

    public function parseErrorProvider(): array
    {
        return [
            [''],
            ['}"jsonrpc":"2.0'],
            [random_bytes(6)]
        ];
    }

    /**
     * @dataProvider invalidInputsProvider
     */
    public function testInvalidInputs(string $in)
    {
        $sut = new Input($in);

        self::assertTrue($sut->parsable());
        self::assertEquals($sut->decoded(), \json_decode($in));

        self::assertFalse($sut->isSingle());
        self::assertFalse($sut->isBatch());
    }

    public function invalidInputsProvider(): array
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
