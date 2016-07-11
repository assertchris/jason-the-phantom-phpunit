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
    ->visit('https://google.co.nz')
    ->resize(1024, 768)
    ->preview();
