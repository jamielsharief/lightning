# Middleware


## CSRF Protection Middleware

> You should also use SameSite cookie attribute for your session cookies to mitigate CSRF attacks, using the Lax setting gives good balance between usuability and security.

This CSRF protection middleware uses the `Synchronizer Token Pattern` whilst providing a unique token per request (not per session). Using a token per request helps mitigate other types of attacks (e.g BREACH).

To create the middleware

```php
$middleware = new CsrfProtectionMiddleware($container->get(SessionInterface::class));
```

The middleware looks for either a header `X-CSRF-Token` or a `csrfToken` form field, when a state changing request has been made, for example `POST`,`PATCH`,`PUT`or `DELETE`. If the token is not provided or the token is not valid a 403 forbidden exception will be thrown.

By default `25` tokens are kept at one time, you can increase or decrease this by setting the setting, wether to make it more usuable or more secure.

```php
$middleware->setMaxTokens(100);
```

By default a CSRF token can only be used once, if you find this is giving you usability issues you can disable this.

```php
$middleware->disableSingleUseTokens();
```

see the [OWASP cross-site request forgery prevention cheat sheet](https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html) to find out more.