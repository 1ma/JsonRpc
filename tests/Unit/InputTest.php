<?php

declare(strict_types=1);

namespace UMA\JsonRpc\Tests\Unit;

use PHPUnit\Framework\TestCase;
use UMA\JsonRpc\Internal\Input;

final class InputTest extends TestCase
{
    /**
     * @dataProvider validInputsProvider
     */
    public function testValidInputs(string $raw): void
    {
        $sut = Input::fromString($raw, false);

        self::assertTrue($sut->parsable());
        self::assertEquals($sut->data(), \json_decode($raw));

        self::assertTrue($sut->isArray() || $sut->isRpcRequest());
    }

    public function validInputsProvider(): array
    {
        return [
            'non empty array' => ['["wut"]'],
            'valid request' => ['{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}'],
            'valid batch request' => ['[{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}]']
        ];
    }

    /**
     * @dataProvider parseErrorProvider
     */
    public function testInputParseError(string $raw): void
    {
        $sut = Input::fromString($raw, false);

        self::assertFalse($sut->parsable());
        self::assertNull($sut->data());

        self::assertFalse($sut->isArray());

        if (!self::simdJsonSupport()) {
            return;
        }

        $sut = Input::fromString($raw, true);

        self::assertFalse($sut->parsable());
        self::assertNull($sut->data());

        self::assertFalse($sut->isArray());
    }

    public function parseErrorProvider(): array
    {
        return [
            [''],
            ['}"jsonrpc":"2.0'],
            [\random_bytes(6)]
        ];
    }

    /**
     * @dataProvider invalidInputsProvider
     */
    public function testInvalidInputs(string $raw): void
    {
        $sut = Input::fromString($raw, false);

        self::assertTrue($sut->parsable());
        self::assertEquals($sut->data(), \json_decode($raw));

        self::assertFalse($sut->isArray());

        if (!self::simdJsonSupport()) {
            return;
        }

        $sut = Input::fromString($raw, true);

        self::assertTrue($sut->parsable());
        self::assertEquals($sut->data(), \json_decode($raw));

        self::assertFalse($sut->isArray());

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

    /**
     * Return whether the PHP installation on which the tests are running has the
     * simdjson extension loaded, is running on Linux and the machine has either
     * the AVX2 or SSE4.2 instruction sets.
     */
    private static function simdJsonSupport(): bool
    {
        return \extension_loaded('simdjson')
            && 'Linux' === PHP_OS
            && (null !== \shell_exec('grep avx2 /proc/cpuinfo') || null !== \shell_exec('grep sse4_2 /proc/cpuinfo'));
    }
}
