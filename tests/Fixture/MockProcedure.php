<?php

declare(strict_types=1);

namespace UMA\JsonRpc\Tests\Fixture;

use UMA\JsonRpc;

class MockProcedure implements JsonRpc\Procedure
{
    public function __invoke(JsonRpc\Request $request): JsonRpc\Response
    {
        if ('get_data' === $request->method()) {
            return new JsonRpc\Success($request->id(), ['hello', 5]);
        }

        return new JsonRpc\Success($request->id());
    }

    public function getSpec(): ?\stdClass
    {
        return null;
    }
}
