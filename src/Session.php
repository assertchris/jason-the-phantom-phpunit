<?php

namespace Undemanding\Client;

use GuzzleHttp\Client;

class Session
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var int
     */
    private $id;

    /**
     * @param Client $client
     * @param int $id
     */
    public function __construct(Client $client, $id)
    {
        $this->client = $client;
        $this->id = $id;
    }

    /**
     * @param Client $client
     *
     * @return Session
     */
    public static function create(Client $client)
    {
        $response = $client->request(
            'POST', '/session'
        );

        $json = json_decode(
            $response->getBody()
        );

        return new static($client, $json->session->id);
    }

    /**
     * @return int
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @return $this
     */
    public function close()
    {
        $this->client->request(
            'POST', sprintf('/session/%s/close', $this->id)
        );

        return $this;
    }
}
