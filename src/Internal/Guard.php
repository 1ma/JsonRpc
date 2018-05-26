<?php

declare(strict_types=1);

namespace UMA\JsonRpc\Internal;

use Opis\JsonSchema\Validator;

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

        return (new Validator())
            ->dataValidation($data, $this->schema)
            ->isValid();
    }
}
