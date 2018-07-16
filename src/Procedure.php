<?php

declare(strict_types=1);

namespace UMA\JsonRpc;

/**
 * Contract for JSON-RPC 2.0 procedures.
 */
interface Procedure
{
    /**
     * Execute the given request.
     */
    public function __invoke(Request $request): Response;

    /**
     * Returns an optional JSON schema object that will
     * be validated against the incoming request parameters.
     */
    public function getSpec(): ?\stdClass;
}
