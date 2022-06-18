<?php

declare(strict_types=1);

namespace UMA\JsonRpc\Internal;

use Opis\JsonSchema\Validator as OpisValidator;
use stdClass;
use function assert;
use function is_array;
use function json_decode;
use function json_encode;
use function json_last_error;

final class Input
{
    private static stdClass $schema;
    private mixed $data;
    private int $error;

    private function __construct(mixed $data, int $error)
    {
        $this->data = $data;
        $this->error = $error;

        // This is the minimal schema that all incoming payloads must
        // conform to in order to be considered well-formed JSON-RPC requests.
        self::$schema = (object)[
            '$schema' => 'https://json-schema.org/draft-07/schema#',
            'description' => 'JSON-RPC 2.0 single request schema',
            'type' => 'object',
            'required' => ['jsonrpc', 'method'],
            'additionalProperties' => false,
            'properties' => (object)[
                'jsonrpc' => (object)[
                    'enum' => ['2.0'],
                ],
                'method' => (object)[
                    'type' => 'string',
                ],
                'params' => (object)[
                    'type' => ['array', 'object'],
                ],
                'id' => (object)[
                    'type' => ['integer', 'string'],
                ],
            ],
        ];
    }

    public static function fromString(string $raw): Input
    {
        return new self(json_decode($raw), json_last_error());
    }

    public static function fromSafeData(mixed $data): Input
    {
        assert(false !== json_encode($data));

        return new self($data, JSON_ERROR_NONE);
    }

    public function data(): mixed
    {
        return $this->data;
    }

    public function parsable(): bool
    {
        return JSON_ERROR_NONE === $this->error;
    }

    public function isArray(): bool
    {
        return is_array($this->data) && !empty($this->data);
    }

    public function isRpcRequest(): bool
    {
        return (new OpisValidator())->validate($this->data, self::$schema)->isValid();
    }
}
