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
        /** @var int[] $numbers */
        $numbers = $request->params();

        // $numbers is *guaranteed* to be an array of integers
        // due to the Json schema defined in method getSpec()
        $sum = \array_reduce($numbers, function(int $partialSum, int $number): int {
            return $partialSum + $number;
        }, 0);

        return new Success($request->id(), $sum);
    }

    public function getSpec(): ?\stdClass
    {
        return \json_decode(<<<'JSON'
{
  "$schema": "https://json-schema.org/draft-07/schema#",

  "type": "array",
  "minItems": 1,
  "items": { "type": "integer" }
}
JSON
        );
    }
}
