<?php

declare(strict_types=1);

namespace UMA\JsonRpc\Internal;

use stdClass;

class Input
{
    /**
     * This is the minimal schema that all incoming payloads must
     * conform to in order to be considered actual JSON-RPC requests.
     */
    private const INPUT_SCHEMA = <<<'JSON'
{
  "$schema": "https://json-schema.org/draft-07/schema#",
  "description": "JSON-RPC 2.0 single request schema",

  "type": "object",
  "required": ["jsonrpc", "method"],
  "additionalProperties": false,
  "properties": {
    "jsonrpc": {
      "type": "string",
      "enum": ["2.0"]
    },
    "method": {
      "type": "string"
    },
    "params": {
      "type": ["array", "object"]
    },
    "id": {
      "type": ["integer", "string"]
    }
  }
}
JSON;

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
        if (!self::$schema instanceof stdClass) {
            self::$schema = \json_decode(self::INPUT_SCHEMA);
        }

        return Validator::validate(self::$schema, $this->data);
    }
}
