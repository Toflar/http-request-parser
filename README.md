# HTTP Request parser

This tiny library parses an HTTP request from its raw string representation to PHP superglobal like arrays.
Typical PHP libraries such as the Symfony HttpFoundation provide ways to represent the current request as an object
by using the PHP globals like so:

```php
<?php

use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();
```

However, if you have a string representation of a request, many of them do not support parsing these.
This library parses the raw HTTP request and provides access to all the superglobals as PHP would create them.
It's easiest to understand by using an example:

```php
<?php

use Toflar\HttpRequestParser\Parser;

$raw = <<<REQUEST
GET /foobar?test=foo%20bar HTTP/1.1
Accept: application/json
Host: example.com
Connection: close
REQUEST;

$parser = new Parser($raw);

var_dump($parser->getGet()); // would output the equivalent of $_GET (encoded as PHP would)
```

You can use the results to create e.g. Symfony requests from these values then:

```php
<?php

use Symfony\Component\HttpFoundation\Request;
use Toflar\HttpRequestParser\Parser;

$raw = <<<REQUEST
GET /foobar?test=foo%20bar HTTP/1.1
Accept: application/json
Host: example.com
Connection: close
REQUEST;

$parser = new Parser($raw);

$request = new Request(
    $parser->getGet(),
    $parser->getServer(),
    [],
    $parser->getCookie(),
    $parser->getFiles(),
    $parser->getServer(),
    $parser->getBody()
);
```
