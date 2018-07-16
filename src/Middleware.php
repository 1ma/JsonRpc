<?php

declare(strict_types=1);

namespace UMA\JsonRpc;

interface Middleware
{
    public function __invoke(Request $request, callable $next): Response;
}
