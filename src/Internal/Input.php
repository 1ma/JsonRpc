<?php

declare(strict_types=1);

namespace UMA\JsonRpc\Internal;

use stdClass;

final class Input
{
    /**
     * @var stdClass
     */
    private static $schema;

    /**
     * @var mixed
     */
    private $data;

    /**
     * @var int
     */
    private $error;

    private function __construct($data, int $error)
    {
        $this->data = $data;
        $this->error = $error;

        // This is the minimal schema that all incoming payloads must
        // conform to in order to be considered well-formed JSON-RPC requests.
        if (!self::$schema instanceof stdClass) {
            self::$schema = (object)[
                '$schema' => 'https://json-schema.org/draft-07/schema#',
                'description' => 'JSON-RPC 2.0 single request schema',
                'type' => 'object',
                'required' => ['jsonrpc', 'method'],
                'additionalProperties' => false,
                'properties' => (object)[
                    'jsonrpc' => (object)[
                        'enum' => ['2.0']
                    ],
                    'method' => (object)[
                        'type' => 'string'
                    ],
                    'params' => (object)[
                        'type' => ['array', 'object']
                    ],
                    'id' => (object)[
                        'type' => ['integer', 'string']
                    ],
                ]
            ];
        }
    }

    public static function fromString(string $raw): Input
    {
        return new self(\json_decode($raw), \json_last_error());
    }

    public static function fromSafeData($data): Input
    {
        \assert(false !== \json_encode($data));

        return new self($data, JSON_ERROR_NONE);
    }

    public function data()
    {
        return $this->data;
    }

    public function parsable(): bool
    {
        return JSON_ERROR_NONE === $this->error;
    }

    public function isArray(): bool
    {
        return \is_array($this->data) && !empty($this->data);
    }

    public function isRpcRequest(): bool
    {
        return Validator::validate(self::$schema, $this->data);
    }
}
