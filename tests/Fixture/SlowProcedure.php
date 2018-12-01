<?php

declare(strict_types=1);

namespace UMA\JsonRpc\Tests\Fixture;

use stdClass;
use UMA\JsonRpc;

/**
 * A pointless procedure that sleeps for the given amount of
 * microseconds and then echoes back the request id.
 *
 * Its purpose is demonstrating that the ConcurrentServer can run a
 * small batch of SlowProcedures in roughly the same time as just one.
 */
class SlowProcedure implements JsonRpc\Procedure
{
    public function execute(JsonRpc\Request $request): JsonRpc\Response
    {
        \usleep($request->params()->wait_time);

        return new JsonRpc\Success($request->id(), $request->id());
    }

    public function getSpec(): ?stdClass
    {
        return \json_decode(<<<'JSON'
{
  "$schema": "https://json-schema.org/draft-07/schema#",

  "type": "object",
  "required": ["wait_time"],
  "additionalProperties": false,
  "properties": {
    "wait_time": { "type": "integer", "minimum": 0 }
  }
}
JSON
        );
    }
}
