<?php

/*
 * This file is part of the toflar/http-request-parser.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright  Yanick Witschi <yanick.witschi@terminal42.ch>
 */

namespace Toflar\HttpRequestParser;

class Parser
{
    /**
     * $_GET.
     *
     * @var array
     */
    private $get = [];

    /**
     * $_POST.
     *
     * @var array
     */
    private $post = [];

    /**
     * $_COOKIE.
     *
     * @var array
     */
    private $cookie = [];

    /**
     * $_FILES.
     *
     * @var array
     */
    private $files = [];

    /**
     * $_SERVER.
     *
     * @var array
     */
    private $server = [];

    /**
     * @var string
     */
    private $body = '';

    /**
     * @var string
     */
    private $raw;

    /**
     * @var bool
     */
    private $parsed = false;

    public function __construct(string $raw)
    {
        $this->raw = $raw;
    }

    public function getGet(): array
    {
        $this->parse();

        return $this->get;
    }

    public function getPost(): array
    {
        $this->parse();

        return $this->post;
    }

    public function getCookie(): array
    {
        $this->parse();

        return $this->cookie;
    }

    public function getFiles(): array
    {
        $this->parse();

        return $this->files;
    }

    public function getServer(): array
    {
        $this->parse();

        return $this->server;
    }

    public function getBody(): string
    {
        $this->parse();

        return $this->body;
    }

    private function parse()
    {
        if ($this->parsed) {
            return;
        }

        if (false !== strpos($this->raw, "\r\n\r\n")) {
            list($headers, $body) = explode("\r\n\r\n", $this->raw, 2);
        } else {
            $headers = $this->raw;
            $body = '';
        }

        $headers = explode("\n", $headers);
        $headers = array_filter(array_map('trim', $headers));

        // Parse first line
        $first = array_shift($headers);
        preg_match('/(?P<method>.*) (?P<path>.*) (?P<protocol>.*)$/', $first, $matches);
        $this->server['REQUEST_METHOD'] = $matches['method'];
        $this->server['REQUEST_URI'] = $matches['path'];
        $this->server['SERVER_PROTOCOL'] = $matches['protocol'];

        // Parse query string
        $this->parsePath($matches['path']);

        // Parse headers
        $this->parseHeaders($headers);

        // Parse body
        $this->parseBody($body);

        $this->parsed = true;
    }

    private function parsePath(string $path)
    {
        if (false !== strpos($path, '?')) {
            list($path, $queryString) = explode('?', $path, 2);
        } else {
            $queryString = '';
        }

        $this->parseQueryString($queryString);
    }

    private function parseHeaders(array $headers): void
    {
        foreach ($headers as $header) {
            list($name, $value) = explode(':', $header, 2);

            // Trim both
            $name = trim($name);
            $value = trim($value);

            // TODO: $_COOKIE
            // TODO: basic auth

            $this->setHeaderToServer($name, $value);
        }
    }

    private function parseBody(string $body): void
    {
        $this->body = $body;

        if ('' === $body) {
            return;
        }

        // TODO: $_POST
        // TODO: $_FILES
    }

    private function parseQueryString(string $queryString): void
    {
        if ('' === $queryString) {
            return;
        }

        // Set $_GET
        $chunks = explode('&', $queryString);

        foreach ($chunks as $chunk) {
            list($key, $value) = explode('=', $chunk, 2);

            // Both, key and value are decoded by PHP for $_GET so we do that here too
            $key = urldecode($key);
            $value = urldecode($value);

            // For the key, PHP replaces spaces by an underscore
            $key = str_replace(' ', '_', $key);

            $this->get[$key] = $value;
        }
    }

    private function setHeaderToServer(string $name, string $value): void
    {
        $name = strtolower($name);
        $nameNormalized = strtoupper(str_replace('-', '_', $name));

        switch ($name) {
            // CONTENT_TYPE and CONTENT_LENGTH are set with and without HTTP_ prefix
            case 'content-type':
            case 'content-length':
                $this->server[$nameNormalized] = $value;
                break;
        }

        $this->server['HTTP_'.$nameNormalized] = $value;
    }
}
