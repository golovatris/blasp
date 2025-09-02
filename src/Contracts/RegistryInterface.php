<?php

namespace Blaspsoft\Blasp\Contracts;

interface RegistryInterface
{
    /**
     * Register an item in the registry.
     *
     * @param string $key
     * @param mixed $item
     * @return void
     */
    public function register(string $key, mixed $item): void;

    /**
     * Retrieve an item from the registry.
     *
     * @param string $key
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function get(string $key): mixed;

    /**
     * Check if an item exists in the registry.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Get all registered items.
     *
     * @return array
     */
    public function all(): array;
}