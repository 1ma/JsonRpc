<?php

declare(strict_types=1);

namespace UMA\RPC\Internal;

abstract class Response implements \JsonSerializable
{
    /**
     * @var int|string|null
     */
    protected $id;
}
