<?php

declare (strict_types=1);

namespace UMA\RPC;

/**
 * Value object representing an incoming JSON-RPC request
 */
class Response
{
    public const PARSE_ERROR = -32700;
    public const INVALID_REQUEST = -32600;
    public const METHOD_NOT_FOUND = -32601;
    public const INVALID_PARAMS = -32602;

    private const ERROR_MESSAGES = [
        self::PARSE_ERROR => 'Parse error',
        self::INVALID_REQUEST => 'Invalid Request',
        self::METHOD_NOT_FOUND => 'Method not found',
        self::INVALID_PARAMS => 'Invalid params'
    ];

    /**
     * @var int|null
     */
    private $code;

    /**
     * @var string|null
     */
    private $result;

    /**
     * @var int|string|null
     */
    private $id;

    private function __construct(?string $result, ?int $id, ?int $code)
    {
        $this->result = $result;
        $this->id = $id;
        $this->code = $code;
    }

    public static function ok(string $result, $id)
    {
        return new self($result, $id, null);
    }

    public static function ko(int $code, $id)
    {
        return new self(null, $id, $code);
    }

    public function __toString()
    {
        if (null !== $this->code) {
            return sprintf(
                '{"jsonrpc":"2.0","error":{"code":%s,"message":"%s"},"id":null}',
                $this->code, static::ERROR_MESSAGES[$this->code]
            );
        }

        if (null === $this->id) {
            return '';
        }

        return sprintf(
            '{"jsonrpc":"2.0","result":%s,"id":%s}',
            $this->result, $this->id
        );
    }
}
