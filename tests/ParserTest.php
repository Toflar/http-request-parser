<?php

/*
 * This file is part of the toflar/http-request-parser.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright  Yanick Witschi <yanick.witschi@terminal42.ch>
 */

namespace Toflar\Psr6HttpCacheStore\Test;

use PHPUnit\Framework\TestCase;
use Toflar\HttpRequestParser\Parser;

class ParserTest extends TestCase
{
    /**
     * @dataProvider parseDataProvider
     */
    public function testParse(array $expGet, array $expPost, array $expServer, array $expFiles, array $expCookie, string $expBody, string $raw)
    {
        $parser = new Parser($raw);

        $this->assertSame($expGet, $parser->getGet());
        $this->assertSame($expPost, $parser->getPost());
        $this->assertSame($expServer, $parser->getServer());
        $this->assertSame($expFiles, $parser->getFiles());
        $this->assertSame($expCookie, $parser->getCookie());
        $this->assertSame($expBody, $parser->getBody());
    }

    public function parseDataProvider(): array
    {
        $provider = [
            'Basic test' => [
                [],
                [],
                [
                    'REQUEST_METHOD' => 'GET',
                    'REQUEST_URI' => '/',
                    'SERVER_PROTOCOL' => 'HTTP/1.1',
                    'HTTP_ACCEPT' => 'application/json',
                ],
                [],
                [],
                '',
            ],
            'Encoded query parameters' => [
                [
                    'foo_bar' => 'what ever',
                ],
                [],
                [
                    'REQUEST_METHOD' => 'GET',
                    'REQUEST_URI' => '/foobar?foo%20bar=what%20ever',
                    'SERVER_PROTOCOL' => 'HTTP/1.1',
                    'HTTP_ACCEPT' => 'application/json',
                ],
                [],
                [],
                '',
            ],
        ];

        foreach (array_keys($provider) as $key) {
            $raw = file_get_contents(__DIR__.'/requests/'.str_replace(' ', '_', strtolower($key)).'.txt');

            $provider[$key][] = $raw;
        }

        return $provider;
    }
}
