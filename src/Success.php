<?php

declare(strict_types=1);

namespace UMA\JsonRpc;

final class Success extends Response
{
    private mixed $result;

    public function __construct(int|string|null $id, mixed $result = null)
    {
        $this->id = $id;
        $this->result = $result;
    }

    public function jsonSerialize(): array
    {
        return [
            'jsonrpc' => '2.0',
            'result' => $this->result,
            'id' => $this->id,
        ];
    }
}
