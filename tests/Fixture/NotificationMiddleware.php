<?php

declare(strict_types=1);

namespace UMA\JsonRpc\Tests\Fixture;

use UMA\JsonRpc;

class NotificationMiddleware implements JsonRpc\Middleware
{
    /**
     * @var boolean
     */
    private $notification;

    public function __invoke(JsonRpc\Request $request, callable $next): JsonRpc\Response
    {
        $this->notification = null === $request->id();

        return $next($request);
    }

    public function lastRequestWasANotification(): bool
    {
        return $this->notification;
    }
}
