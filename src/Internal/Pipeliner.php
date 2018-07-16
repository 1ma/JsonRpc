<?php

declare(strict_types=1);

namespace UMA\JsonRpc\Internal;

use Psr\Container\ContainerInterface;
use UMA\JsonRpc;

/**
 * Helper class to compose the pipeline of middlewares + procedure
 */
class Pipeliner
{
    /**
     * @param ContainerInterface $container
     * @param JsonRpc\Procedure  $procedure
     * @param string[]           $services
     *
     * @return callable
     */
    public static function build(ContainerInterface $container, JsonRpc\Procedure $procedure, array $services): callable
    {
        $pipe = $procedure;

        foreach ($services as $service => $_) {
            $middleware = $container->get($service);

            if (!$middleware instanceof JsonRpc\Middleware) {
                throw new \RuntimeException('Expected an instance of UMA\\JsonRpc\\Middleware');
            }

            $pipe = self::connect($middleware, $pipe);
        }

        return $pipe;
    }

    private static function connect(JsonRpc\Middleware $outer, callable $inner): callable
    {
        return function (JsonRpc\Request $request) use ($outer, $inner): JsonRpc\Response {
            return $outer($request, $inner);
        };
    }
}
