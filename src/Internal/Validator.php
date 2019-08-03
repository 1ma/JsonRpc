<?php

declare(strict_types=1);

namespace UMA\JsonRpc\Internal;

use Opis\JsonSchema\Validator as OpisValidator;
use stdClass;

final class Validator
{
    /**
     * @param stdClass|null $schema The schema to check against the given data.
     * @param mixed         $data   The data to validate (MUST be decoded JSON data).
     *
     * @return bool Whether $data conforms to $schema or not
     */
    public static function validate(stdClass $schema, $data): bool
    {
        \assert(false !== \json_encode($data));

        return (new OpisValidator)
            ->dataValidation($data, $schema)
            ->isValid();
    }
}
