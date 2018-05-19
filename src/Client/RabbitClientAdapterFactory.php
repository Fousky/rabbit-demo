<?php

namespace App\Client;

use App\Configuration\RabbitConfiguration;
use Bunny\Client;

/**
 * @author Lukáš Brzák <lukas.brzak@fousky.cz>
 */
final class RabbitClientAdapterFactory
{
    /** @var RabbitConfiguration */
    private $rabbitConfiguration;

    public function __construct(RabbitConfiguration $rabbitConfiguration)
    {
        $this->rabbitConfiguration = $rabbitConfiguration;
    }

    public function getAdapter(string $vhost = '/'): RabbitClientAdapter
    {
        return new RabbitClientAdapter(
            $this->createClient($vhost)
        );
    }

    private function createClient(string $vhost): Client
    {
        $client = new Client([
            'vhost' => $vhost,
            'host' => $this->rabbitConfiguration->getHost(),
            'port' => (int) $this->rabbitConfiguration->getPort(),
            'user' => $this->rabbitConfiguration->getUsername(),
            'pass' => $this->rabbitConfiguration->getPassword(),
        ]);

        $client->connect();

        return $client;
    }
}
