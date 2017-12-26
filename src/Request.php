<?php

namespace UMA\RPC;

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

    /**
     * Precondition: $input has passed request.json validation.
     */
    public function __construct(Input $input)
    {
        $input = $input->decoded();

        $this->id = $input->id ?? null;
        $this->method = $input->method;
        $this->params = $input->params ?? null;
    }

    /**
     * @return int|string|null
     */
    public function getId()
    {
        return $this->id;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return \stdClass|array|null
     */
    public function getParams()
    {
        return $this->params;
    }
}
