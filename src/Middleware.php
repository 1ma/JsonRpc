<?php

declare(strict_types=1);

namespace UMA\JsonRpc;

/**
 * Contract for JSON-RPC 2.0 middlewares.
 */
interface Middleware
{
    /**
     * Run some code before or after the target Procedure. Within the body
     * of this method, at some point you MUST call $next($request)
     * and return the result.
     */
    public function __invoke(Request $request, Procedure $next): Response;
}
