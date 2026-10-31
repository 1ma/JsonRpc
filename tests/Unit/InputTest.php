<?php

declare(strict_types=1);

namespace UMA\JsonRpc\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use UMA\JsonRpc\Internal\Input;

final class InputTest extends TestCase
{
    #[DataProvider('validInputsProvider')]
    public function testValidInputs(string $raw): void
    {
        $sut = Input::fromString($raw);

        self::assertTrue($sut->parsable());
        self::assertEquals($sut->data(), json_decode($raw));

        self::assertTrue($sut->isArray() || $sut->isRpcRequest());
    }

    public static function validInputsProvider(): array
    {
        return [
            'non empty array' => ['["wut"]'],
            'valid request' => ['{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}'],
            'valid batch request' => ['[{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}]'],
        ];
    }

    #[DataProvider('parseErrorProvider')]
    public function testInputParseError(string $raw): void
    {
        $sut = Input::fromString($raw);

        self::assertFalse($sut->parsable());
        self::assertNull($sut->data());

        self::assertFalse($sut->isArray());
    }

    public static function parseErrorProvider(): array
    {
        return [
            [''],
            ['}"jsonrpc":"2.0'],
            [random_bytes(6)],
        ];
    }

    #[DataProvider('invalidInputsProvider')]
    public function testInvalidInputs(string $raw): void
    {
        $sut = Input::fromString($raw);

        self::assertTrue($sut->parsable());
        self::assertEquals($sut->data(), json_decode($raw));

        self::assertFalse($sut->isArray());
    }

    public static function invalidInputsProvider(): array
    {
        return [
            'null' => ['null'],
            'boolean' => ['true'],
            'integer' => ['1'],
            'double' => ['1.2'],
            'string' => ['"foo"'],
            'empty string' => ['""'],
            'empty array' => ['[]'],
        ];
    }
}
