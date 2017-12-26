<?php

namespace UMA\RPC\Internal;

class Request
{
    /**
     * @var int|string|null
     */
    private $id;

    /**
     * @var string
     */
    private $method;

    /**
     * @var \stdClass|array|null
     */
    private $params;

    public function __construct(Input $input)
    {
        \assert($input->konforms());

        $this->id = $input->decoded()->id ?? null;
        $this->method = $input->decoded()->method;
        $this->params = $input->decoded()->params ?? null;
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
     * @return \stdClass|array|null
     */
    public function params()
    {
        return $this->params;
    }
}
