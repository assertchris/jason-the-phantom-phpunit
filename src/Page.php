<?php

namespace Undemanding\Client;

use GuzzleHttp\Client;
use InvalidArgumentException;
use Undemanding\Client\Exception\Assertion;

class Page
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $eventId = 1;

    /**
     * @param Client $client
     * @param Session $session
     * @param int $id
     */
    public function __construct(Client $client, Session $session, $id)
    {
        $this->client = $client;
        $this->session = $session;
        $this->id = $id;
    }

    /**
     * @param Client $client
     * @param Session $session
     *
     * @return Page
     */
    public static function create(Client $client, Session $session)
    {
        $response = $client->request(
            "POST", "/session/" . $session->id() . "/page"
        );

        $json = json_decode(
            $response->getBody()
        );

        return new static($client, $session, $json->page->id);
    }

    /**
     * @return int
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function returned()
    {
        return $this->view()->returned;
    }

    /**
     * @return mixed
     */
    private function view()
    {
        $response = $this->client->request(
            "GET", $this->endpoint("view")
        );

        $json = json_decode(
            $response->getBody()
        );

        return $json->page;
    }

    /**
     * @param string $type
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    private function endpoint($type)
    {
        if ($type == "list") {
            return "/session/" . $this->session->id() . "/page";
        }

        if ($type == "view") {
            return "/session/" . $this->session->id() . "/page/" . $this->id;
        }

        $actions = [
            "zoom",
            "resize",
            "scroll",
            "capture",
            "run",
            "visit",
            "wait",
        ];

        if (in_array($type, $actions)) {
            return "/session/" . $this->session->id() . "/page/" . $this->id . "/" . $type;
        }

        throw new InvalidArgumentException("unsupported type");
    }

    /**
     * @return mixed
     */
    public function address()
    {
        return $this->view()->address;
    }

    /**
     * @return mixed
     */
    public function status()
    {
        return $this->view()->status;
    }

    /**
     * @return mixed
     */
    public function body()
    {
        return $this->view()->body;
    }

    /**
     * @param null|int $width
     *
     * @return int|Page
     */
    public function width($width = null)
    {
        if ($width) {
            if ($height = $this->height()) {
                return $this->resize($width, $height);
            }
        }

        return $this->view()->width;
    }

    /**
     * @param null|int $height
     *
     * @return int|Page
     */
    public function height($height = null)
    {
        if ($height) {
            if ($width = $this->width()) {
                return $this->resize($width, $height);
            }
        }

        return $this->view()->height;
    }

    /**
     * @param int $width
     * @param int $height
     *
     * @return $this
     */
    public function resize($width, $height)
    {
        $this->client->request(
            "POST", $this->endpoint("resize"), [
                "form_params" => [
                    "width" => $width,
                    "height" => $height,
                ],
            ]
        );

        return $this;
    }

    /**
     * @param null|int $left
     *
     * @return int|Page
     */
    public function left($left = null)
    {
        if ($left) {
            if ($top = $this->top()) {
                return $this->scroll($left, $top);
            }
        }

        return $this->view()->left;
    }

    /**
     * @param null|int $top
     *
     * @return int|Page
     */
    public function top($top = null)
    {
        if ($top) {
            if ($left = $this->left()) {
                return $this->scroll($left, $top);
            }
        }

        return $this->view()->top;
    }

    /**
     * @param int $left
     * @param int $top
     *
     * @return $this
     */
    public function scroll($left, $top)
    {
        $this->client->request(
            "POST", $this->endpoint("scroll"), [
                "form_params" => [
                    "left" => $left,
                    "top" => $top,
                ],
            ]
        );

        return $this;
    }

    /**
     * @param null|int $zoom
     *
     * @return int|Page
     */
    public function zoom($zoom = null)
    {
        if ($zoom) {
            $this->client->request(
                "POST", $this->endpoint("zoom"), [
                    "form_params" => [
                        "zoom" => $zoom,
                    ],
                ]
            );

            return $this;
        }

        return $this->view()->zoom;
    }

    /**
     * @param string $address
     *
     * @return $this
     */
    public function visit($address)
    {
        $this->client->request(
            "POST", $this->endpoint("visit"), [
                "form_params" => [
                    "address" => $address,
                ],
            ]
        );

        return $this;
    }

    /**
     * @param string $script
     *
     * @return $this
     */
    public function run($script)
    {
        $this->client->request(
            "POST", $this->endpoint("run"), [
                "form_params" => [
                    "script" => $script,
                ],
            ]
        );

        return $this;
    }

    /**
     * @param int $frequency
     * @param int $timeout
     *
     * @return $this
     */
    public function wait($frequency = 50, $timeout = 250)
    {
        $this->client->request(
            "POST", $this->endpoint("wait"), [
                "form_params" => [
                    "frequency" => $frequency,
                    "timeout" => $timeout,
                ],
            ]
        );

        return $this;
    }

    /**
     * @return string
     */
    public function capture()
    {
        $response = $this->client->request(
            "POST", $this->endpoint("capture")
        );

        $json = json_decode(
            $response->getBody()
        );

        return $json->data;
    }

    /**
     * @param string $expected
     *
     * @return $this
     *
     * @throws Assertion
     */
    public function see($expected)
    {
        $body = $this->body();

        if (strpos($body, $expected) === false) {
            throw new Assertion("'" . $expected . "' not found");
        }

        return $this;
    }

    /**
     * @param string $expected
     *
     * @return $this
     *
     * @throws Assertion
     */
    public function doNotSee($expected)
    {
        $body = $this->body();

        if (strpos($body, $expected) !== false) {
            throw new Assertion("'" . $expected . "' found");
        }

        return $this;
    }

    /**
     * @param string $selector
     *
     * @return $this
     */
    public function click($selector)
    {
        $nextId = $this->eventId++;

        $this->run("
            var event" . $nextId . " = document.createEvent('MouseEvent');

            event" . $nextId . ".initMouseEvent(
                'click',
                true /* bubbles */,
                true /* cancelable */,
                window
            );

            var element" . $nextId . " = document.querySelector('" . $selector . "');

            element" . $nextId . ".dispatchEvent(event" . $nextId . ");
        ");

        return $this;
    }

    /**
     * Displays a preview image of the current screen.
     *
     * @return $this
     */
    public function preview()
    {
        $name = "/tmp/" . time() . ".png";

        $data = $this->capture();
        file_put_contents($name, base64_decode($data));

        @exec("/usr/bin/qlmanage -p " . $name . " 2> /dev/null");

        unlink($name);

        return $this;
    }
}
