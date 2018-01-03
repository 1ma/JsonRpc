<?php

declare(strict_types=1);

namespace UMA\JsonRpc;

abstract class Response implements \JsonSerializable
{
    /**
     * @var int|string|null
     */
    protected $id;
}
