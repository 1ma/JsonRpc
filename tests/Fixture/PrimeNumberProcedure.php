<?php

declare(strict_types=1);

namespace UMA\JsonRpc\Tests\Fixture;

use stdClass;
use UMA\JsonRpc;

final class PrimeNumberProcedure implements JsonRpc\Procedure
{
    public function __invoke(JsonRpc\Request $request): JsonRpc\Response
    {
        return new JsonRpc\Success($request->id(), 'this is a prime number');
    }

    public function getSpec(): ?stdClass
    {
        return \json_decode(
            <<<'JSON'
                {
                  "$schema": "https://json-schema.org/draft-07/schema#",

                  "type": "object",
                  "required": ["number"],
                  "additionalProperties": false,
                  "properties": {
                    "number": {
                      "type": "integer",
                      "format": "prime"
                    }
                  }
                }
                JSON
        );
    }
}
