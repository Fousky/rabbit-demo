<?php

namespace App\Client;

use RabbitMq\ManagementApi\Client;

/**
 * @author Lukáš Brzák <lukas.brzak@fousky.cz>
 */
final class RabbitManagementClientAdapter
{
    /** @var Client */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function getVirtualHosts(): array
    {
        return $this->client->vhosts()->all();
    }

    public function createVirtualHost(string $name)
    {
        $this->client->vhosts()->create($name);
    }

    public function deleteVirtualHost(string $name)
    {
        $this->client->vhosts()->delete($name);
    }

    public function createQueue(string $vhost, string $name, array $options)
    {
        $this->client->queues()->create($vhost, $name, $options);
    }

    public function getQueues(string $vhost = null): array
    {
        return $this->client->queues()->all($vhost);
    }

    public function deleteQueue(string $vhost, string $queueName)
    {
        $this->client->queues()->delete($vhost, $queueName);
    }

    public function createBindings(
        string $vhost,
        string $exchange,
        string $queue,
        string $routingKey = null
    ) {
        $this->client->bindings()->create(
            $vhost,
            $exchange,
            $queue,
            $routingKey
        );
    }

    public function getBindings(string $vhost, string $exchange, string $queue, string $props): array
    {
        return $this->client->bindings()->get($vhost, $exchange, $queue, $props);
    }

    public function deleteBindings(
        string $vhost,
        string $exchange,
        string $queue,
        string $props
    )
    {
        $this->client->bindings()->delete($vhost, $exchange, $queue, $props);
    }

    public function whoami(): array
    {
        return $this->client->whoami();
    }
}
