<?php

namespace App\Client;

use App\Model\ProducerMessage;
use Bunny\Channel;
use Bunny\Client;

/**
 * @author Lukáš Brzák <lukas.brzak@fousky.cz>
 */
final class RabbitClientAdapter
{
    /** @var Client */
    private $client;

    /** @var Channel|null */
    private $channel;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function publish(ProducerMessage $message)
    {
        // open new Channel on-demand.
        $channel = $this->getChannel();

        $channel->publish(
            $message->getBody(),
            $message->getHeaders(),
            $message->getExchange(),
            $message->getRoutingKey(),
            $message->isMandatory(),
            $message->isImmediate()
        );
    }

    public function getChannel(): Channel
    {
        if ($this->channel === null) {
            $channel = $this->client->channel();
            if (!$channel instanceof Channel) {
                throw new \RuntimeException('Invalid configuration.');
            }
            $this->channel = $channel;
        }

        return $this->channel;
    }

    public function __destruct()
    {
        // close channel on-destruct.
        if ($this->channel) {
            $this->channel->close();
        }
    }
}
