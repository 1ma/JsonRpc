<?php

declare(strict_types=1);

namespace UMA\RPC\Internal;

use League\JsonGuard\Validator;

class Guard
{
    /**
     * @var \stdClass
     */
    private $schema;

    public function __construct(\stdClass $schema)
    {
        $this->schema = $schema;
    }

    public function __invoke($data): bool
    {
        \assert(false !== \json_encode($data));

        return (new Validator($data, $this->schema))->passes();
    }
}
