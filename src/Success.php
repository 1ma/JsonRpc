<?php

declare(strict_types=1);

namespace UMA\RPC;

class Success extends Response
{
    /**
     * @var mixed|null
     */
    private $result;

    /**
     * @param int|string $id
     * @param mixed|null $result
     */
    public function __construct($id, $result = null)
    {
        $this->id = $id;
        $this->result = $result;
    }

    public function __toString()
    {
        return \json_encode([
            'jsonrpc' => '2.0',
            'result' => $this->result,
            'id' => $this->id
        ]);
    }
}
