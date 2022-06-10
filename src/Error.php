<?php

declare(strict_types=1);

namespace UMA\JsonRpc;

final class Error extends Response
{
    /**
     * @var int
     */
    private $code;

    /**
     * @var string
     */
    private $message;

    /**
     * @var mixed
     */
    private $data;

    /**
     * @param int             $code
     * @param string          $message
     * @param mixed           $data
     * @param int|string|null $id
     */
    public function __construct(int $code, string $message, $data = null, $id = null)
    {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
        $this->id = $id;
    }

    public static function parsing(): Error
    {
        return new static(-32700, 'Parse error');
    }

    public static function invalidRequest(): Error
    {
        return new static(-32600, 'Invalid Request');
    }

    public static function unknownMethod($id): Error
    {
        return new static(-32601, 'Method not found', null, $id);
    }

    public static function invalidParams($id, mixed $data = null): Error
    {
        return new static(-32602, 'Invalid params', $data, $id);
    }

    public static function internal($id): Error
    {
        return new static(-32603, 'Internal error', null, $id);
    }

    public static function tooManyBatchRequests(int $limit): Error
    {
        return new static(-32000, 'Too many batch requests sent to server', ['limit' => $limit]);
    }

    public function jsonSerialize(): array
    {
        $payload = [
            'jsonrpc' => '2.0',
            'error' => [
                'code' => $this->code,
                'message' => $this->message
            ],
            'id' => $this->id
        ];

        if (null !== $this->data) {
            $payload['error']['data'] = $this->data;
        }

        return $payload;
    }
}
