<?php

declare(strict_types=1);

namespace UMA\JsonRpc\Tests\Fixture;

use stdClass;
use UMA\JsonRpc;
use function array_reduce;
use function json_decode;

final class Adder implements JsonRpc\Procedure
{
    public function __invoke(JsonRpc\Request $request): JsonRpc\Response
    {
        // $request->params() is *guaranteed* to be an array of
        // integers due to the JsonSchema defined in getSpec()
        $sum = array_reduce(
            $request->params(),
            fn (int $partialSum, int $number): int => $partialSum + $number,
            0
        );

        return new JsonRpc\Success($request->id(), $sum);
    }

    public function getSpec(): ?stdClass
    {
        return json_decode(
            <<<'JSON'
                {
                  "$schema": "https://json-schema.org/draft-07/schema#",

                  "type": "array",
                  "items": { "type": "integer" }
                }
                JSON
        );
    }
}
