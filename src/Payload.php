<?php

declare(strict_types=1);

namespace UMA\RPC;

/**
 * Payload objects decode raw JSON payloads and answer
 * relevant questions about it.
 */
class Payload
{
    /**
     * @var mixed
     */
    private $data;

    /**
     * @var bool
     */
    private $error;

    public function __construct(string $raw)
    {
        $this->data = \json_decode($raw);
        $this->error = \json_last_error();
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
     * Returns whether the raw payload was a valid JSON
     * string and therefore could be decoded, or not.
     */
    public function parsable(): bool
    {
        return JSON_ERROR_NONE === $this->error;
    }

    /**
     * Returns whether the decoded payload _looks like_
     * a single Remote Procedure Call.
     */
    public function isSingle(): bool
    {
        return $this->data instanceof \stdClass;
    }

    /**
     * Returns whether the decoded payload _looks like_
     * a batch Remote Procedure Call.
     */
    public function isBatch(): bool
    {
        return \is_array($this->data) && !empty($this->data);
    }
}
