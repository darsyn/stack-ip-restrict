# IP Address [![Build Status](https://travis-ci.org/darsyn/stack-ip-restrict.svg?branch=master)](https://travis-ci.org/darsyn/ip)

HTTP Kernel middleware for restriction applications, both blacklisting and whitelisting, from IP ranges.

## Installation

Use [Composer](http://getcomposer.org):

```bash
composer require darsyn/stack-ip-restrict
```

### Requirements

Although this library *should* work with PHP 5.3, it is not officially supported since it reached end-of-life in 2014.

As of version `2.0.0`, this library supports Symfony 3 but as a result the lowest version it supports is `2.8.0`.

## Example Usage

```php
<?php

use Darsyn\Stack\IpRestrict\Blacklist;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;

$kernel = new Kernel();

$stackedApp = new Blacklist($kernel, $listedIpAddresses);
// Set a custom response object to send when access is denied, if you so wish:
$stackedApp->setAccessDeniedResponse(new Response(
    'Uh-uh, no website until you pay up!',
    402
));

$request = Request::createFromGlobals();
$response = $stackedApp->handle($request);
$response->send();
$kernel->terminate();
```

The **IpRestrict** HTTP Kernel middleware is compatible with [StackPHP](http://stackphp.com)'s
[Builder](https://github.com/stackphp/builder):

```php
<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;

$kernel = new Kernel();

$stackedApp = (new Stack\Builder)
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
