<?php

require __DIR__ . "/../vendor/autoload.php";

use Undemanding\Client\Session;
use Undemanding\Client\Page;

$client = new GuzzleHttp\Client(["base_uri" => "http://localhost:4321"]);

$session = Session::create($client);
$page = Page::create($client, $session);

function printLine($message) {
    print $message . "\n";
}

function testExpectation($positive, $negative, $label) {
    try {
        $positive();
        printLine("'" . $label . "' positive test good");
    } catch (PHPUnit_Framework_ExpectationFailedException $exception) {
        printLine("'" . $label . "' positive test bad");
    }

    try {
        $negative();
        printLine("'" . $label . "' negative test bad");
    } catch (PHPUnit_Framework_ExpectationFailedException $exception) {
        printLine("'" . $label . "' negative test good");
    }
}

testExpectation(
    function() use ($page) {
        $page->run("document.body.innerHTML = 'hello world'")->see("hello world");
    },
    function() use ($page) {
        $page->run("document.body.innerHTML = 'hello world'")->see("foo");
    },
    "see"
);

testExpectation(
    function() use ($page) {
        $page->run("document.body.innerHTML = 'hello world'")->doNotSee("foo");
    },
    function() use ($page) {
        $page->run("document.body.innerHTML = 'hello world'")->doNotSee("hello world");
    },
    "doNotSee"
);

testExpectation(
    function() use ($page) {
        $page->run("
                document.body.innerHTML = '<button>normal</button>';

                setTimeout(function() {
                    var button = document.querySelector('button');

                    button.addEventListener('click', function() {
                        document.body.innerHTML = 'clicked';
                    });
                }, 0);
            ")
            ->click("button")
            ->see("clicked");
    },
    function() use ($page) {
        $page->run("
                document.body.innerHTML = '';
            ")
            ->click("missing")
            ->see("clicked");
    },
    "click"
);
