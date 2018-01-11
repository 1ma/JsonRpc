<?php

declare(strict_types=1);

namespace UMA\JsonRpc\Tests\Fixture;

use UMA\JsonRpc\Request;
use UMA\JsonRpc\Response;
use UMA\JsonRpc\Procedure;
use UMA\JsonRpc\Success;

class Adder implements Procedure
{
    public function execute(Request $request): Response
    {
        $accumulator = 0;

        /** @var int[] $numbers */
        $numbers = $request->params();

        foreach ($numbers as $integer) {
            $accumulator += $integer;
        }

        return new Success($request->id(), $accumulator);
    }

    public function getSpec(): ?\stdClass
    {
        return \json_decode(<<<'JSON'
{
  "$schema": "http://json-schema.org/schema#",

  "type": "array",
  "minItems": 1,
  "items": { "type": "integer" }
}
JSON
        );
    }
}
