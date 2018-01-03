<?php

declare(strict_types=1);

namespace UMA\JsonRpc;

/**
 * Contract for JSON-RPC 2.0 procedures.
 */
interface Procedure
{
    public function execute(Request $request): Response;

    public function getSpec(): ?\stdClass;
}
