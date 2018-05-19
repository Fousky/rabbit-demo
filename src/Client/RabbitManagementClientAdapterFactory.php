<?php

namespace App\Client;

use App\Configuration\RabbitConfiguration;
use RabbitMq\ManagementApi\Client;

/**
 * @author Lukáš Brzák <lukas.brzak@fousky.cz>
 */
final class RabbitManagementClientAdapterFactory
{
    /** @var RabbitConfiguration */
    private $rabbitConfiguration;

    public function __construct(RabbitConfiguration $rabbitConfiguration)
    {
        $this->rabbitConfiguration = $rabbitConfiguration;
    }

    public function getAdapter(): RabbitManagementClientAdapter
    {
        return new RabbitManagementClientAdapter(
            $this->createClient()
        );
    }

    private function createClient(): Client
    {
        return new Client(
            null,
            $this->rabbitConfiguration->getApiUrl(),
            $this->rabbitConfiguration->getUsername(),
            $this->rabbitConfiguration->getPassword()
        );
    }
}
