<?php

declare(strict_types=1);

namespace UMA\RPC;

use Psr\Container\ContainerInterface;
use UMA\RPC\Internal\Guard;
use UMA\RPC\Internal\Input;
use UMA\RPC\Internal\Request;
use UMA\RPC\Internal\Response;

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
        if (!$this->container->has($serviceId)) {
            throw new \LogicException("Cannot find service '$serviceId' in the container");
        }

        $this->methods[$method] = $serviceId;

        return $this;
    }

    public function run(string $raw): ?string
    {
        $input = Input::fromString($raw);

        if (!$input->parsable()) {
            return $this->end(Error::parsing());
        }

        if ($input->isArray()) {
            return $this->batch($input);
        }

        return $this->single($input);
    }

    private function batch(Input $input): ?string
    {
        \assert(\is_array($input->decoded()));

        $responses = [];
        foreach ($input->decoded() as $request) {
            $pseudoInput = Input::fromSafeData($request);

            if(null !== $response = $this->single($pseudoInput)) {
                $responses[] = $response;
            }
        }

        return empty($responses) ?
            null : \sprintf('[%s]', \implode(',', $responses));
    }

    private function single(Input $input): ?string
    {
        if (!$input->isRpcRequest()) {
            return $this->end(Error::invalidRequest());
        }

        $request = new Request($input);

        if (!isset($this->methods[$request->method()])) {
            return $this->end(Error::unknownMethod($request->id()), $request);
        }

        $procedure = $this->container->get($this->methods[$request->method()]);

        if (!$procedure instanceof Procedure) {
            return $this->end(Error::internal($request->id()), $request);
        }

        $spec = $procedure->getSpec();

        if ($spec instanceof \stdClass && !(new Guard($spec))($request->params())) {
            return $this->end(Error::invalidParams($request->id()), $request);
        }

        return $this->end($procedure->execute($request), $request);
    }

    private function end(Response $response, Request $request = null): ?string
    {
        return $request instanceof Request && null === $request->id() ?
            null : \json_encode($response);
    }
}
