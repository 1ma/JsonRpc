<?php

declare(strict_types=1);

namespace UMA\RPC;

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

    private function __construct($data, int $error)
    {
        $this->data = $data;
        $this->error = $error;
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

    /**
     * Returns the decoded JSON data.
     *
     * @return mixed
     */
    public function decoded()
    {
        return $this->data;
    }

    /**
     * Returns whether the raw input was a valid JSON
     * string and therefore could be decoded, or not.
     */
    public function parsable(): bool
    {
        return JSON_ERROR_NONE === $this->error;
    }

    /**
     * Returns whether the decoded input _looks like_
     * a single Remote Procedure Call.
     */
    public function isSingle(): bool
    {
        return $this->data instanceof \stdClass;
    }

    /**
     * Returns whether the decoded input _looks like_
     * a batch Remote Procedure Call.
     */
    public function isBatch(): bool
    {
        return \is_array($this->data) && !empty($this->data);
    }
}
