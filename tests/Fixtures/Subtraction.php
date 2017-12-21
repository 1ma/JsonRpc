<?php

namespace UMA\RPC\Tests\Fixtures;

use UMA\RPC\Procedure;
use UMA\RPC\Request;
use UMA\RPC\Response;

class Subtraction implements Procedure
{
    public function herp(): ?\stdClass
    {
        return null;
    }

    public function execute(Request $request): Response
    {
        [$minuend, $subtrahend] = $request->getParams();

        $result = $minuend - $subtrahend;

        return Response::ok(json_encode($result), $request->getId());
    }
}
