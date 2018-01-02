<?php

declare(strict_types=1);

namespace UMA\RPC\Tests\Fixture;

use UMA\RPC\Internal\Request;
use UMA\RPC\Internal\Response;
use UMA\RPC\Procedure;
use UMA\RPC\Success;

class Subtractor implements Procedure
{
    public function execute(Request $request): Response
    {
        $params = $request->params();

        if ($params instanceof \stdClass) {
            $minuend = $params->minuend;
            $subtrahend = $params->subtrahend;
        } else {
            [$minuend, $subtrahend] = $params;
        }

        return new Success($request->id(), $minuend - $subtrahend);
    }

    public function getSpec(): ?\stdClass
    {
        return \json_decode(<<<'JSON'
{
  "$schema": "http://json-schema.org/schema#",

  "type": ["array", "object"],
  "minItems": 2,
  "maxItems": 2,
  "items": { "type": "integer" },
  "required": ["minuend", "subtrahend"],
  "additionalProperties": false,
  "properties": {
    "minuend": { "type": "integer" },
    "subtrahend": { "type": "integer" }
  }
}
JSON
);
    }
}
