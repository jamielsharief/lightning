# Cookie


## Cookie Object

A `Cookie` object can be created like this, the default path is `/` and the default max age is `0` which means until the browser is closed.

```php
$cookie = new Cookie('foo','bar');
```

Setter methods

```php
$cookie->setMaxAge(3600);  // Default for cookie is 0
$cookie->setHttpOnly(true);
$cookie->setSecure(true);
$cookie->setPath('/articles/'); 
$cookie->setDomain('example.com')
$cookie->setSameSite('Lax');

// to add the cookie to a Response object
$response = $cookie->addToResponse($response);
```

## Cookies

The `Cookies` object helps reading cookies from the request and writing to the response.

```php
$cookies = new Cookies();
$cookies->setServerRequest($request); // Sets the Request object, cookies will be read from here

$result = $cookies->has('foo');
$value = $cookies->get('foo');

// to add a cookie which later will be added to the Response
$cookie = new Cookie('bar','1234');
$cookie->setMaxAge(3600);
$cookies->add($cookie);

// To delete a cookie pass a cookie with the name
$cookies->delete(new Cookie('bar')); 

$response = $cookies->addToResponse($response);
```

## CookieMiddleware

The `Cookies` object will be attached to the `ServerRequest`

```php
$cookies = new Cookies();
$middleware = new CookieMiddleware($cookies);
$cookies = $request->getAttribute('cookies');
```

If you are using DI (dependency injection), then make sure that you use the same instance

```php
$cookies = $container->get(Cookies::class);
$middleware = new CookieMiddleware($cookies);
```