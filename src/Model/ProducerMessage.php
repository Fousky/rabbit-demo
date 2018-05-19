<?php

namespace App\Model;

/**
 * @author Lukáš Brzák <lukas.brzak@fousky.cz>
 */
class ProducerMessage
{
    const EXCHANGE_DIRECT = 'amq.direct';
    const EXCHANGE_FANOUT = 'amq.fanout';
    const EXCHANGE_HEADERS = 'amq.headers';
    const EXCHANGE_MATCH = 'amq.match';
    const EXCHANGE_TRACE = 'amq.rabbitmq.trace';
    const EXCHANGE_TOPIC = 'amq.topic';

    public static $exchanges = [
        self::EXCHANGE_DIRECT,
        self::EXCHANGE_FANOUT,
        self::EXCHANGE_HEADERS,
        self::EXCHANGE_MATCH,
        self::EXCHANGE_TRACE,
        self::EXCHANGE_TOPIC,
    ];

    protected $body = [];
    protected $headers = [];
    protected $exchange = self::EXCHANGE_DIRECT;
    protected $routingKey = '';
    protected $mandatory = false;
    protected $immediate = false;

    public function getBody(): string
    {
        return \GuzzleHttp\json_encode($this->body);
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getExchange(): string
    {
        return $this->exchange;
    }

    public function getRoutingKey(): string
    {
        return $this->routingKey;
    }

    public function isMandatory(): bool
    {
        return $this->mandatory === true;
    }

    public function isImmediate(): bool
    {
        return $this->immediate === true;
    }

    public function setBody(array $body): ProducerMessage
    {
        $this->body = $body;

        return $this;
    }

    public function addHeader(string $key, $value): ProducerMessage
    {
        $this->headers[$key] = $value;

        return $this;
    }

    public function setHeaders(array $headers): ProducerMessage
    {
        $this->headers = $headers;

        return $this;
    }

    public function setRoutingKey(string $routingKey): ProducerMessage
    {
        $this->routingKey = $routingKey;

        return $this;
    }

    public function setExchange(string $exchange): ProducerMessage
    {
        $this->exchange = $exchange;

        return $this;
    }

    public function setMandatory(bool $mandatory): ProducerMessage
    {
        $this->mandatory = $mandatory;

        return $this;
    }

    public function setImmediate($immediate): ProducerMessage
    {
        $this->immediate = $immediate;

        return $this;
    }
}
