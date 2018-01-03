<?php

declare(strict_types=1);

namespace UMA\JsonRpc\Tests\Fixture\Procedure;

use UMA\JsonRpc\Request;
use UMA\JsonRpc\Response;
use UMA\JsonRpc\Procedure;
use UMA\JsonRpc\Success;

class MockProcedure implements Procedure
{
    public function execute(Request $request): Response
    {
        if ('get_data' === $request->method()) {
            return new Success($request->id(), ['hello', 5]);
        }

        return new Success($request->id());
    }

    public function getSpec(): ?\stdClass
    {
        return null;
    }
}
