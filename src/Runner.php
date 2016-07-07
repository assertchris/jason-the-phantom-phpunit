<?php

namespace Undemanding\Client;

class Runner
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var null|int
     */
    private $pid = null;

    /**
     * @param string $path
     * @param string $host
     * @param int $port
     */
    public function __construct($path, $host, $port)
    {
        $this->path = $path;
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * Start the Undemanding server.
     */
    public function start()
    {
        $address = "http://" . $this->host . ":" . $this->port;

        if (!$this->running($address)) {
            $path = $this->path;
            $script = $path . "/vendor/undemanding/client/node_modules/undemanding-server/src/server.js";

            $command =
                "UNDEMANDING_SERVER_HOST=" . $this->host
                . " UNDEMANDING_SERVER_PORT=" . $this->port
                . " node " . $script . " > /dev/null & echo $!";

            exec($command, $output);
            $this->pid = $output[0];

            sleep(1);
        }
    }

    /**
     * @param string $address
     *
     * @return bool
     */
    private function running($address) {
        $response = @file_get_contents($address);

        return strpos($response, "Everything is ok!") !== false;
    }

    /**
     * Stop the Undemanding server.
     */
    public function stop()
    {
        if ($this->pid) {
            $command = "kill {$this->pid} > /dev/null 2> /dev/null";
            @exec($command, $output);
        }
    }
}
