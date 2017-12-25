<?php

declare(strict_types=1);

namespace UMA\RPC;

class Error extends Response
{
    private const ERROR_TABLE = [
        -32000 => 'Server error',
        -32600 => 'Invalid Request',
        -32601 => 'Method not found',
        -32602 => 'Invalid params',
        -32603 => 'Internal error',
        -32700 => 'Parse error'
    ];

    /**
     * @var int
     */
    private $code;

    /**
     * @var mixed|null
     */
    private $data;

    /**
     * @param int             $code
     * @param int|string|null $id
     * @param mixed|null      $data
     */
    private function __construct(int $code, $id = null, $data = null)
    {
        $this->id = $id;
        $this->code = $code;
        $this->data = $data;
    }

    public static function parsing(): Error
    {
        return new static(-32700);
    }

    public static function invalidRequest(): Error
    {
        return new static(-32600);
    }

    /**
     * @param int|string $id
     */
    public static function unknownMethod($id): Error
    {
        return new static(-32601, $id);
    }

    /**
     * @param int|string $id
     */
    public static function invalidParams($id): Error
    {
        return new static(-32602, $id);
    }

    public static function userDefined($id, $data): Error
    {
        return new static(-32000, $id, $data);
    }

    /**
     * @param int|string $id
     */
    public static function internal($id): Error
    {
        return new static(-32603, $id);
    }

    public function __toString()
    {
        $response = [
            'jsonrpc' => '2.0',
            'error' => [
                'code' => $this->code,
                'message' => static::ERROR_TABLE[$this->code]
            ],
            'id' => $this->id
        ];

        if (null !== $this->data) {
            $response['error']['data'] = $this->data;
        }

        return \json_encode($response);
    }
}
