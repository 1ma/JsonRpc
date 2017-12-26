<?php

namespace UMA\RPC;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use UMA\RPC\Internal\Input;
use UMA\RPC\Internal\Request;

class Server
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $methods;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->methods = [];
    }

    public function add(string $method, string $serviceId): Server
    {
        if (isset($this->methods[$method])) {
            throw new \LogicException('');
        }

        if (!$this->container->has($serviceId)) {
            throw new \LogicException('');
        }

        $this->methods[$method] = $serviceId;

        return $this;
    }

    public function run(string $raw): ?string
    {
        $input = Input::fromString($raw);

        if (!$input->parsable()) {
            return \json_encode(Error::parsing());
        }

        if ($input->isSingle()) {
            return $this->processSingle($input);
        }

        if ($input->isBatch()) {
            return $this->processBatch($input);
        }

        return \json_encode(Error::invalidRequest());
    }

    private function processSingle(Input $input): ?string
    {
        if (!$input->konforms()) {
            return \json_encode(Error::invalidRequest());
        }

        $request = new Request($input);

        if (!isset($this->methods[$request->method()])) {
            return null === $request->id() ?
                null : \json_encode(Error::unknownMethod($request->id()));
        }

        $serviceId = $this->methods[$request->method()];

        try {
            $procedure = $this->container->get($serviceId);
        } catch (ContainerExceptionInterface|NotFoundExceptionInterface $e) {
            return null === $request->id() ?
                null : \json_encode(Error::internal($request->id()));
        }

        if (!$procedure instanceof Procedure) {
            return null === $request->id() ?
                null : \json_encode(Error::internal($request->id()));
        }

        $response = $procedure->execute($request);

        return null === $request->id() ?
            null : \json_encode($response);
    }

    private function processBatch(Input $input): ?string
    {
        \assert(\is_array($input->decoded()));

        $responses = [];
        foreach ($input->decoded() as $request) {
            $pseudoInput = Input::fromSafeData($request);

            if(null !== $response = $this->processSingle($pseudoInput)) {
                $responses[] = $response;
            }
        }

        return empty($responses) ?
            null : \json_encode($responses);
    }
}
