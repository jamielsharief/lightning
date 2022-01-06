# Cookie


## Cookie Object

A `Cookie` object

```php
$cookie = new Cookie('foo','bar');

$cookie->setHttpOnly(true);
$cookie->setSecure(true);
$cookie->setPath('/');
$cookie->setDomain('example.com')
$cookie->setSameSite('Lax');

$response = $cookie->addToResponse($response);
```

## Cookies Manager

```php
$cookies = new Cookies();
$cookies->setServerRequest($request); // Sets the Request object, cookies will be read from here

$result = $cookies->has('foo');
$value = $cookies->get('foo');


$cookies->add(new Cookie('bar','1234'));
$response = $cookies->addToResponse($response);
```

## CookieMiddleware

The `Cookies` object will be attached to the `ServerRequest`

```php
$middleware = new CookieMiddleware(new Cookies());

$request->getAttribute('cookies');
```

If you are using DI (dependency injection), then make sure you use the same instance

```php
$middleware = new CookieMiddleware($container->get(Cookies::class));
```