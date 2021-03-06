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
            'POST', sprintf('/session/%s/page', $session->id())
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
            'GET', $this->endpoint('view')
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
        if ($type == 'list') {
            return sprintf('/session/%s/page', $this->session->id());
        }

        if ($type == 'view') {
            return sprintf('/session/%s/page/%s', $this->session->id(), $this->id);
        }

        $actions = [
            'zoom',
            'resize',
            'scroll',
            'capture',
            'run',
            'visit',
            'wait',
        ];

        if (in_array($type, $actions)) {
            return sprintf('/session/%s/page/%s/%s', $this->session->id(), $this->id, $type);
        }

        throw new InvalidArgumentException(sprintf('"%s" is not supported', $type));
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
            'POST', $this->endpoint('resize'), [
                'form_params' => [
                    'width' => $width,
                    'height' => $height,
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
            'POST', $this->endpoint('scroll'), [
                'form_params' => [
                    'left' => $left,
                    'top' => $top,
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
                'POST', $this->endpoint('zoom'), [
                    'form_params' => [
                        'zoom' => $zoom,
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
            'POST', $this->endpoint('visit'), [
                'form_params' => [
                    'address' => $address,
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
            'POST', $this->endpoint('run'), [
                'form_params' => [
                    'script' => $script,
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
            'POST', $this->endpoint('wait'), [
                'form_params' => [
                    'frequency' => $frequency,
                    'timeout' => $timeout,
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
            'POST', $this->endpoint('capture')
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
            throw new Assertion(sprintf('"%s" not found', $expected));
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
            throw new Assertion(sprintf('"%s" found', $expected));
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
        $this->run(sprintf(
            '$("%s")
                .trigger("mousedown")
                .trigger("mouseup")
                .trigger("click")
            ;',
            addslashes($selector)
        ));

        return $this;
    }

    /**
     * @param string $selector
     * @param string $value
     *
     * @return $this
     */
    public function fill($selector, $value)
    {
        $this->run(sprintf(
            '$("%s").val("%s");',
            addslashes($selector),
            addslashes($value)
        ));

        foreach (str_split($value) as $index => $character) {
            $properties = sprintf(
                '{keyCode: "%1$s".charCodeAt(0), key: "%1$s", which: "%1$s"}',
                $character
            );

            $this->run(sprintf(
                '$("%1$s")
                    .trigger("keydown", %2$s)
                    .trigger("keyup", %2$s)
                    .trigger("keypress", %2$s)
                ;',
                addslashes($selector),
                $properties
            ));
        }

        $this->run(sprintf('$("%s").trigger("change");', addslashes($selector)));

        return $this;
    }

    /**
     * Displays a preview image of the current screen.
     *
     * @return $this
     */
    public function preview()
    {
        $name = sprintf('/tmp/%s.png', time());
        $data = $this->capture();

        file_put_contents($name, base64_decode($data));
        exec(sprintf('/usr/bin/qlmanage -p %s > /dev/null 2> /dev/null', $name));
        unlink($name);

        return $this;
    }
}
