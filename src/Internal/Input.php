<?php

declare(strict_types=1);

namespace UMA\RPC\Internal;

class Input
{
    /**
     * @var mixed
     */
    private $data;

    /**
     * @var bool
     */
    private $error;

    /**
     * @var bool
     */
    private $isRpcRequest;

    private function __construct($data, int $error)
    {
        $this->data = $data;
        $this->error = $error;

        $this->isRpcRequest = $this->parsable() && (new Guard(
            \json_decode(file_get_contents(__DIR__.'/../../spec/request.json'))
        ))($this->decoded());
    }

    public static function fromString(string $raw): Input
    {
        return new static(\json_decode($raw), \json_last_error());
    }

    public static function fromSafeData($data): Input
    {
        \assert(false !== \json_encode($data));

        return new static($data, JSON_ERROR_NONE);
    }

    public function decoded()
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
        return $this->isRpcRequest;
    }
}
