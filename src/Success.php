<?php

declare(strict_types=1);

namespace UMA\JsonRpc;

class Success extends Response
{
    /**
     * @var mixed|null
     */
    private $result;

    /**
     * @param int|string|null $id
     * @param mixed|null      $result
     */
    public function __construct($id, $result = null)
    {
        $this->id = $id;
        $this->result = $result;
    }

    public function jsonSerialize(): array
    {
        return [
            'jsonrpc' => '2.0',
            'result' => $this->result,
            'id' => $this->id
        ];
    }
}
