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
     * of this method, you MUST call $next($request) somewhere and return
     * that result (it is guaranteed to be a Response).
     */
    public function __invoke(Request $request, callable $next): Response;
}
