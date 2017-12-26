<?php

namespace UMA\RPC;

use League\JsonGuard\Validator as JsonGuard;

class Guard
{
    private $schema;

    public function __construct(\stdClass $schema)
    {
        $this->schema = $schema;
    }

    public function __invoke($object): bool
    {
        return (new JsonGuard($object, $this->schema))->passes();
    }
}
