<?php

declare(strict_types=1);

namespace UMA\JsonRpc;

final class Error extends Response
{
    private int|string|null $code;
    private string $message;
    private mixed $data;

    public function __construct(int $code, string $message, mixed $data = null, int|string|null $id = null)
    {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
        $this->id = $id;
    }

    public static function parsing(): Error
    {
        return new Error(-32700, 'Parse error');
    }

    public static function invalidRequest(): Error
    {
        return new Error(-32600, 'Invalid Request');
    }

    public static function unknownMethod($id): Error
    {
        return new Error(-32601, 'Method not found', null, $id);
    }

    public static function invalidParams($id, mixed $data = null): Error
    {
        return new Error(-32602, 'Invalid params', $data, $id);
    }

    public static function internal($id): Error
    {
        return new Error(-32603, 'Internal error', null, $id);
    }

    public static function tooManyBatchRequests(int $limit): Error
    {
        return new Error(-32000, 'Too many batch requests sent to server', ['limit' => $limit]);
    }

    public function jsonSerialize(): array
    {
        $payload = [
            'jsonrpc' => '2.0',
            'error' => [
                'code' => $this->code,
                'message' => $this->message,
            ],
            'id' => $this->id,
        ];

        if (null !== $this->data) {
            $payload['error']['data'] = $this->data;
        }

        return $payload;
    }
}
