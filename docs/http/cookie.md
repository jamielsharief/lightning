# Cookie


## Cookie Object

A `Cookie` object

```php
$cookie = new Cookie('foo','bar');

$cookie->setMaxAge(3600);  // Default for cookie is -1
$cookie->setHttpOnly(true);
$cookie->setSecure(true);
$cookie->setPath('/'); 
$cookie->setDomain('example.com')
$cookie->setSameSite('Lax');

$response = $cookie->addToResponse($response);
```

## Cookies

```php
$cookies = new Cookies();
$cookies->setServerRequest($request); // Sets the Request object, cookies will be read from here

$result = $cookies->has('foo');
$value = $cookies->get('foo');

// to add a cookie
$cookie = new Cookie('bar','1234');
$cookie->setMaxAge(3600);
$cookies->add($cookie);

// To delete a cookie, create a new cookie without setting max-age
$cookies->add(new Cookie('delete-me')); 

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