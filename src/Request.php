<?php

declare (strict_types=1);

namespace UMA\RPC;

class Request
{
    public function getMethod(): string
    {
        return 'foo';
    }

    /**
     * @return array|\stdClass|null
     */
    public function getParams()
    {
        return null;
    }

    /**
     * @return int|string|null
     */
    public function getId()
    {
        return null;
    }
}
