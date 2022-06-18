<?php

declare(strict_types=1);

namespace UMA\JsonRpc;

use stdClass;
use UMA\JsonRpc\Internal\Input;
use function assert;

final class Request implements \JsonSerializable
{
    private mixed $raw;
    private int|string|null $id;
    private string $method;
    private stdClass|array|null $params;

    public function __construct(Input $input)
    {
        assert($input->isRpcRequest());

        $this->raw = $input->data();

        $this->id = $this->raw->id ?? null;
        $this->method = $this->raw->method;
        $this->params = $this->raw->params ?? null;
    }

    public function id(): int|string|null
    {
        return $this->id;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function params(): stdClass|array|null
    {
        return $this->params;
    }

    public function jsonSerialize(): mixed
    {
        return $this->raw;
    }
}
