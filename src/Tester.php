<?php

namespace Undemanding\Client;

use GuzzleHttp\Client;
use LogicException;

trait Tester
{
    /**
     * @var null|Runner
     */
    private static $undemandingRunner;

    /**
     * @var null|Client
     */
    private $undemandingClient = null;

    /**
     * @var null|Session
     */
    private $undemandingSession = null;

    /**
     * Start the Undemanding server.
     */
    public static function undemandingStart()
    {
        $host = static::undemandingHost();
        $port = static::undemandingPort();
        $path = static::undemandingPath();

        if (!static::$undemandingRunner) {
            static::$undemandingRunner = new Runner($path, $host, $port);
        }

        static::$undemandingRunner->start();
    }

    /**
     * @return string
     *
     * @throws LogicException
     */
    public static function undemandingHost()
    {
        if ($host = getenv("UNDEMANDING_CLIENT_HOST")) {
            return $host;
        }

        throw new LogicException("UNDEMANDING_CLIENT_HOST not defined");
    }

    /**
     * @return string
     *
     * @throws LogicException
     */
    public static function undemandingPort()
    {
        if ($port = getenv("UNDEMANDING_CLIENT_PORT")) {
            return $port;
        }

        throw new LogicException("UNDEMANDING_CLIENT_PORT not defined");
    }

    /**
     * @return string
     *
     * @throws LogicException
     */
    public static function undemandingPath()
    {
        if ($path = getenv("UNDEMANDING_CLIENT_PATH")) {
            return $path;
        }

        throw new LogicException("UNDEMANDING_CLIENT_PATH not defined");
    }

    /**
     * @param Runner $runner
     *
     * @return null|Runner
     */
    public static function undemandingRunner(Runner $runner = null)
    {
        if ($runner) {
            static::$undemandingRunner = $runner;
        }

        return static::$undemandingRunner;
    }

    /**
     * Stop the Undemanding server.
     */
    public static function undemandingStop()
    {
        if (static::$undemandingRunner) {
            static::$undemandingRunner->stop();
        }
    }

    /**
     * @param string $address
     *
     * @return Page
     */
    protected function visit($address)
    {
        $base = sprintf(
            'http://%s:%s',
            static::undemandingHost(),
            static::undemandingPort()
        );

        if (!$this->undemandingClient) {
            $this->undemandingClient = $this->createUndemandingClient($base);
        }

        if (!$this->undemandingSession) {
            $this->undemandingSession = $this->createUndemandingSession($this->undemandingClient);
        }

        return $this->createUndemandingPage($this->undemandingClient, $this->undemandingSession)->visit($address);
    }

    /**
     * @param string $base
     *
     * @return Client
     */
    private function createUndemandingClient($base)
    {
        return new Client([
            'base_uri' => $base,
        ]);
    }

    /**
     * @param Client $client
     *
     * @return Session
     */
    private function createUndemandingSession(Client $client)
    {
        return Session::create($client);
    }

    /**
     * @param Client $client
     * @param Session $session
     *
     * @return Page
     */
    private function createUndemandingPage(Client $client, Session $session)
    {
        return Page::create($client, $session);
    }
}
