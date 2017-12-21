<?php

namespace UMA\RPC\Tests\Fixtures;

class StrictSubtraction extends Subtraction
{
    public function herp(): ?\stdClass
    {
        return json_decode(<<<'SCHEMA'
{
  "$schema": "http://json-schema.org/schema#",

  "type": "array",
  "minItems": 2,
  "maxItems": 2,
  "items": {
    "type": "integer"
  }
}
SCHEMA
        );
    }
}
