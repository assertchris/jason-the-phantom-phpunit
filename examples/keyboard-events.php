<?php

require __DIR__ . '/../vendor/autoload.php';

use Undemanding\Client\Page;
use Undemanding\Client\Session;

$client = new GuzzleHttp\Client([
    'base_uri' => 'http://localhost:4321',
]);

$session = Session::create($client);
$page = Page::create($client, $session);

$page
    ->run('$(document.body).html("<textarea></textarea>")')
    ->fill('textarea', 'hello world')
    ->preview();
