<?php

declare(strict_types=1);

namespace UMA\RPC;

use UMA\RPC\Internal\Request;
use UMA\RPC\Internal\Response;

interface Procedure
{
    public function execute(Request $request): Response;

    public function paramSpec(): ?\stdClass;
}
