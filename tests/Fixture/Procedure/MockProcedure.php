<?php

declare(strict_types=1);

namespace UMA\RPC\Tests\Fixture\Procedure;

use UMA\RPC\Internal\Request;
use UMA\RPC\Internal\Response;
use UMA\RPC\Procedure;
use UMA\RPC\Success;

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
