<?php

declare(strict_types=1);

namespace UMA\JsonRpc\Tests\Fixture;

use UMA\JsonRpc;

class LoggingMiddleware implements JsonRpc\Middleware
{
    /**
     * @var string[]
     */
    private $requests;

    /**
     * @var string[]
     */
    private $responses;

    public function __invoke(JsonRpc\Request $request, callable $next): JsonRpc\Response
    {
        $this->requests[] = \json_encode($request);

        $response =  $next($request);

        $this->responses[] = \json_encode($response);

        return $response;
    }

    public function getSeenRequests(): array
    {
        return $this->requests;
    }

    public function getSeenResponses(): array
    {
        return $this->responses;
    }
}
