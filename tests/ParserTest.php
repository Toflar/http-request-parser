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
            'Basic auth' => [
                [],
                [],
                [
                    'REQUEST_METHOD' => 'GET',
                    'REQUEST_URI' => '/',
                    'SERVER_PROTOCOL' => 'HTTP/1.1',
                    'PHP_AUTH_USER' => 'Aladdin',
                    'PHP_AUTH_PW' => 'open sesame',
                    'HTTP_AUTHORIZATION' => 'Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==',
                ],
                [],
                [],
                '',
            ],
            'Digest auth' => [
                [],
                [],
                [
                    'REQUEST_METHOD' => 'GET',
                    'REQUEST_URI' => '/',
                    'SERVER_PROTOCOL' => 'HTTP/1.1',
                    'PHP_AUTH_DIGEST' => 'username="Mufasa", realm="testrealm@host.com", nonce="dcd98b7102dd2f0e8b11d0f600bfb0c093", uri="/dir/index.html", qop=auth, nc=00000001, cnonce="0a4f113b", response="6629fae49393a05397450978507c4ef1", opaque="5ccc069c403ebaf9f0171e9517f40e41"',
                    'HTTP_AUTHORIZATION' => 'Digest username="Mufasa", realm="testrealm@host.com", nonce="dcd98b7102dd2f0e8b11d0f600bfb0c093", uri="/dir/index.html", qop=auth, nc=00000001, cnonce="0a4f113b", response="6629fae49393a05397450978507c4ef1", opaque="5ccc069c403ebaf9f0171e9517f40e41"',
                ],
                [],
                [],
                '',
            ],
            'Cookies' => [
                [],
                [],
                [
                    'REQUEST_METHOD' => 'GET',
                    'REQUEST_URI' => '/',
                    'SERVER_PROTOCOL' => 'HTTP/1.1',
                    'HTTP_COOKIE' => 'name=value; name2=value2; name3=value3; name4=value%20foobar'
                ],
                [],
                [
                    'name' => 'value',
                    'name2' => 'value2',
                    'name3' => 'value3',
                    'name4' => 'value foobar',
                ],
                '',
            ],
            'Form post' => [
                [],
                [
                    'foo' => 'bar',
                    'foo2' => 'bar 20',
                ],
                [
                    'REQUEST_METHOD' => 'POST',
                    'REQUEST_URI' => '/',
                    'SERVER_PROTOCOL' => 'HTTP/1.1',
                    'CONTENT_TYPE' => 'application/x-www-form-urlencoded; charset=utf-8',
                    'HTTP_CONTENT_TYPE' => 'application/x-www-form-urlencoded; charset=utf-8',
                    'CONTENT_LENGTH' => '19',
                    'HTTP_CONTENT_LENGTH' => '19',
                ],
                [],
                [],
                'foo=bar&foo2=bar+20',
            ],
        ];

        foreach (array_keys($provider) as $key) {
            $raw = file_get_contents(__DIR__.'/requests/'.str_replace(' ', '_', strtolower($key)).'.txt');

            $provider[$key][] = $raw;
        }

        return $provider;
    }
}
