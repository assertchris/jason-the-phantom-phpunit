<?php

namespace Undemanding\Client;

use Exception;

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
     * @var string[]
     */
    private $pid = [];

    /**
     * @var bool
     */
    private $run = false;

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
        if ($this->run) {
            return;
        }

        $this->run = true;

        if (!$this->addressAvailable($this->host, $this->port)) {
            throw new Exception('Address in use');
        }

        $hash = spl_object_hash($this);

        $script = sprintf(
            '%s/vendor/undemanding/client/node_modules/undemanding-server/src/server.js',
            $this->path
        );

        $this->exec(sprintf(
            'hash=%s UNDEMANDING_SERVER_HOST=%s UNDEMANDING_SERVER_PORT=%s node %s',
            $hash, $this->host, $this->port, $script
        ));

        $output = $this->exec(
            sprintf('ps -o pid,command | grep %s', $hash), $silent = false, $background = false
        );

        if (empty($output)) {
            throw new Exception('No processes running');
        }

        foreach ($output as $line) {
            $parts = explode(' ', $line);
            $this->pid[] = $parts[0];
        }

        sleep(1);
    }

    /**
     * Runs a command silently and in the background.
     *
     * @param string $command
     *
     * @param bool $silent
     * @param bool $background
     *
     * @return array|string
     */
    private function exec($command, $silent = true, $background = true)
    {
        if ($silent) {
            $command .= ' > /dev/null 2> /dev/null';
        }

        if ($background) {
            $command .= ' &';
        }

        exec($command, $output);

        return $output;
    }

    /**
     * Check if something is running at a specific host/port.
     *
     * @param string $host
     * @param int $port
     *
     * @return bool
     */
    private function addressAvailable($host, $port)
    {
        $handle = curl_init($host);

        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($handle, CURLOPT_HEADER, true);
        curl_setopt($handle, CURLOPT_NOBODY, true);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_PORT, $port);

        $response = curl_exec($handle);

        curl_close($handle);

        if (empty($response)) {
            return true;
        }

        return false;
    }

    /**
     * Stop the Undemanding server.
     */
    public function stop()
    {
        foreach ($this->pid as $pid) {
            $this->exec(sprintf('kill -9 %s', $pid));
        }
    }
}
