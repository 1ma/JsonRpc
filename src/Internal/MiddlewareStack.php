<?php

declare(strict_types=1);

namespace UMA\JsonRpc\Internal;

use stdClass;
use UMA\JsonRpc;

final class MiddlewareStack implements JsonRpc\Procedure
{
    /**
     * @var JsonRpc\Middleware
     */
    private $middleware;

    /**
     * @var JsonRpc\Procedure
     */
    private $next;

    public static function compose(JsonRpc\Procedure $bottom, JsonRpc\Middleware ...$middlewares): JsonRpc\Procedure
    {
        $stack = $bottom;

        foreach ($middlewares as $middleware) {
            $stack = new self($middleware, $stack);
        }

        return $stack;
    }

    private function __construct(JsonRpc\Middleware $middleware, JsonRpc\Procedure $next)
    {
        $this->middleware = $middleware;
        $this->next = $next;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(JsonRpc\Request $request): JsonRpc\Response
    {
        return $this->middleware->process($request, $this->next);
    }

    /**
     * {@inheritdoc}
     */
    public function getSpec(): ?stdClass
    {
        return null;
    }
}
