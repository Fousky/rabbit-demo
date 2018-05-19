<?php

namespace App\Configuration;

/**
 * @author Lukáš Brzák <lukas.brzak@fousky.cz>
 */
final class RabbitConfiguration
{
    /** @var string */
    private $host;

    /** @var string */
    private $port;

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /** @var string */
    private $apiUrl;

    public function __construct(
        string $host,
        string $port,
        string $username,
        string $password,
        string $apiUrl
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->apiUrl = $apiUrl;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): string
    {
        return $this->port;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }
}
