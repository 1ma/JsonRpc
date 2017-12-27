<?php

declare(strict_types=1);

namespace UMA\RPC;

use UMA\RPC\Internal\Request;
use UMA\RPC\Internal\Response;

/**
 * Contract for JSON-RPC 2.0 procedures.
 */
interface Procedure
{
    public function execute(Request $request): Response;

    public function paramSpec(): ?\stdClass;
}
