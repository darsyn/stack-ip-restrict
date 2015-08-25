# IP Address [![Build Status](https://travis-ci.org/darsyn/ip.svg?branch=master)](https://travis-ci.org/darsyn/ip)

HTTP Kernel middleware for restriction applications, both blacklisting and whitelisting, from IP ranges.

## Installation

Use [Composer](http://getcomposer.org):

```bash
composer require darsyn/stack-ip-restrict
```

### Requirements

Although this library *should* work with PHP 5.3, it is not officially supported since it reached end-of-life in 2014.

## Example Usage

```php
<?php

use Darsyn\Stack\IpRestrict\Blacklist;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;

$kernel = new Kernel();

$stackedApp = new Blacklist($kernel, $listedIpAddresses);

$request = Request::createFromGlobals();
$response = $stackedApp->handle($request);
$response->send();
$kernel->terminate();
```

The **IpRestrict** HTTP Kernel middleware is compatible with [StackPHP](http://stackphp.com)'s
[Builder](https://github.com/stackphp/builder):

```php
<?php

use StackPHP\Builder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;

$kernel = new Kernel();

$stackedApp = (new MiddlewareBuilder)
    ->push('Darsyn\Stack\IpRestrict\Blacklist', $listedIpAddresses)
    // All your other middlewares...
    ->resolve($kernel);

$request = Request::createFromGlobals();
$response = $stackedApp->handle($request);
$response->send();
$kernel->terminate();
```

## License

Please see the [separate license file](LICENSE.md) included in this repository for a full copy of the MIT license,
which this project is licensed under.

## Authors

- [Zander Baldwin](https://zanderbaldwin.com).

If you make a contribution (submit a pull request), don't forget to add your name here!