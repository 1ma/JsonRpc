<?php

declare(strict_types=1);

namespace UMA\RPC;

abstract class Response implements \JsonSerializable
{
    /**
     * @var int|string|null
     */
    protected $id;

    /**
     * @var int|string|null
     */
    public function getId()
    {
        return $this->id;
    }
}
