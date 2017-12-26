<?php

namespace UMA\RPC;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

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

    /**
     * @var Guard
     */
    private $rqGuard;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->methods = [];
        $this->rqGuard = new Guard(\json_decode(
            \file_get_contents(__DIR__ . '/../spec/request.json')
        ));
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
        if (!($this->rqGuard)($input->decoded())) {
            return \json_encode(Error::invalidRequest());
        }

        $request = new Request($input);

        if (!isset($this->methods[$request->getMethod()])) {
            return null === $request->getId() ?
                null : \json_encode(Error::unknownMethod($request->getId()));
        }

        $serviceId = $this->methods[$request->getMethod()];

        try {
            $procedure = $this->container->get($serviceId);
        } catch (ContainerExceptionInterface|NotFoundExceptionInterface $e) {
            return null === $request->getId() ?
                null : \json_encode(Error::internal($request->getId()));
        }

        if (!$procedure instanceof Procedure) {
            return null === $request->getId() ?
                null : \json_encode(Error::internal($request->getId()));
        }

        $response = $procedure->execute($request);

        return null === $request->getId() ?
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
