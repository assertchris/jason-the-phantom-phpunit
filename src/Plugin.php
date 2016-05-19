<?php

namespace Undemanding\Client;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

class Plugin implements PluginInterface
{
    public function activate(Composer $composer, IOInterface $io)
    {
        $previous = getcwd();

        chdir(realpath(__DIR__ . "/../"));
        exec("npm install");

        chdir($previous);
    }
}
