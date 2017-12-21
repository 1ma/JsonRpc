<?php

namespace UMA\RPC\Tests\Fixtures;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ArrayContainer implements ContainerInterface
{
    /**
     * @var array
     */
    private $services;

    public function __construct(array $services)
    {
        $this->services = $services;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (false === $this->has($id)) {
            throw new class extends \RuntimeException implements NotFoundExceptionInterface {};
        }

        return $this->services[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        return isset($this->services[$id]);
    }
}
