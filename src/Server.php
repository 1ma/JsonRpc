<?php

declare (strict_types=1);

namespace UMA\RPC;

use League\JsonGuard\Validator;
use Psr\Container\ContainerInterface;

class Server
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string[]
     */
    private $methods;

    /**
     * @var \stdClass
     */
    private $innerSchema;

    /**
     * @var \stdClass
     */
    private $outerSchema;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->innerSchema = json_decode(file_get_contents(__DIR__ . '/../schemas/inner.json'));
        $this->outerSchema = json_decode(file_get_contents(__DIR__ . '/../schemas/outer.json'));
    }

    public function register(string $method, string $service): Server
    {
        if (isset($this->methods[$service])) {
            throw new \LogicException('fack');
        }

        if (false === $this->container->has($service)) {
            throw new \LogicException('off');
        }

        $this->methods[$method] = $service;

        return $this;
    }

    public function process(string $data): string
    {
        $decoded = json_decode($data);

        if (JSON_ERROR_NONE !== json_last_error()) {
            return '{"jsonrpc":"2.0","error":{"code":-32700,"message":"Parse error"},"id":null}';
        }

        if ((new Validator($decoded, $this->outerSchema))->fails()) {
            return '{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null}';
        }

        if ($decoded instanceof \stdClass) {
            return $this->handle($decoded);
        }

        $batch = [];
        /** @var array $decoded */
        foreach ($decoded as $item) {
            $batch[] = $this->handle($item);
        }

        return sprintf('[%s]', implode(',', $batch));
    }

    /**
     * @param mixed $item
     *
     * @return string
     */
    private function handle($item): string
    {
        if ((new Validator($item, $this->innerSchema))->fails()) {
            return '{"jsonrpc":"2.0","error":{"code":-32600,"message":"Invalid Request"},"id":null}';
        }

        return '';
    }
}
