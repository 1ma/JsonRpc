<?php

declare(strict_types=1);

namespace UMA\JsonRpc\Tests\Fixture\Psr11;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ArrayContainer implements ContainerInterface
{
    /**
     * @var string[]
     */
    private $services;

    public function __construct(array $services = [])
    {
        $this->services = $services;
    }

    /**
     * {@inheritdoc}
     */
    public function has($id): bool
    {
        return array_key_exists($id, $this->services);
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (!$this->has($id)) {
            throw new class extends \RuntimeException implements NotFoundExceptionInterface {};
        }

        return $this->services[$id];
    }
}
