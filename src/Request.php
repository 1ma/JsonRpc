<?php

declare(strict_types=1);

namespace UMA\JsonRpc;

use stdClass;
use UMA\JsonRpc\Internal\Input;

final class Request implements \JsonSerializable
{
    /**
     * @var mixed
     */
    private $raw;

    /**
     * @var int|string|null
     */
    private $id;

    /**
     * @var string
     */
    private $method;

    /**
     * @var stdClass|array|null
     */
    private $params;

    public function __construct(Input $input)
    {
        \assert($input->isRpcRequest());

        $this->raw = $input->data();

        $this->id = $this->raw->id ?? null;
        $this->method = $this->raw->method;
        $this->params = $this->raw->params ?? null;
    }

    /**
     * @return int|string|null
     */
    public function id()
    {
        return $this->id;
    }

    public function method(): string
    {
        return $this->method;
    }

    /**
     * @return stdClass|array|null
     */
    public function params()
    {
        return $this->params;
    }

    public function jsonSerialize()
    {
        return $this->raw;
    }
}
