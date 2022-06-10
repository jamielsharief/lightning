# Authentication

The Authentication package provides middleware components for authentication and authorization

## Identity Service

The identity service is for handling the user details lookup, you can use the `PdoIdentityService` or attach the `IdentityServiceInterface` to your existing repository or create your own identity service which may use a different storage type.

```php
$identityService = (new PdoIdentityService($container->get(PDO::class)))
    ->setTable('identities') // database table name
    ->setIdentifierName('username');  // the name/column for the identifier, e.g. username, email , token etc
    ->setCredentialName('password');  // this is the password name/column e.g. password, hashed_password
```

## Password Hashers

We provide `Argon2` and `Bcrypt` password hashers, and the `PasswordHasherInterface` so you can create a custom passwoder hasher. 

OWSAP recommends to use Argon2 ,and if that is not available then Bcrypt. See [OWASP Password Storage Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html)

## Middlewares

Some of the authentication middlewares will return a response, therefore for those ones you will need to provide an empty `PSR-7 Response` object.

### Login Form

The `FormAuthenticationMiddleware` is for managing authentication and authorization for web applications using a login form, once the user logs in, the user details are stored in session under the `identity` key.

```php
$identityService = $container->get(IdentityServiceInterface::class);
$session = $container->get(SessionInterface::class);

$middleware (new FormAuthenticationMiddleware($identityService, new BcryptPasswordHasher(), $session, new ResponseFactory()))
    ->setLoginPath('/login')
    ->setUsernameField('email')
    ->setPasswordField('password');
```

The `LoginFormAuthentication` allows the login path request to be displayed, and will only accept credentials that are sent by a `POST` request to the `loginPath`.

```php
$middleware->setLoginPath('/login');
```

Here is what it might look like in the controller.

```php
public function login(ServerRequestInterface $request): ResponseInterface
{
    if ($request->getMethod() === 'POST') {

        // user is now logged in
        if($identity = $request->getAttribute('identity')){
            return $this->redirect('/articles/index');
        }
    }

    return $this->render('/login');
}

public function logout(SessionInterface $session): ResponseInterface
{
    $session->clear();
    $session->regenerateId();

    return $this->redirect('/login');
}
```

By default an `UnauthorizedException` is thrown if the user tries to access a resource that requires authentication, however if you want them to redirect to another page, then you will need to set the unauthenticated redirect.

```php
$middleware->setUnauthenticatedRedirect('/login');
```

### Token Authentication

The `TokenAuthenticationMiddleware` allows you authenticate using an API token either in the URL query param or a header

To use a query param e.g. `/api/status?token=xxxxx`

```php
(new TokenAuthenticationMiddleware($container->get(IdentityServiceInterface::class)))
    ->setQueryParam('token');
```

To search for the token in the headers

```php
(new TokenAuthenticationMiddleware($container->get(IdentityServiceInterface::class)))
    ->setHeader('X-API-Token')
```

### HTTP Basic Authentication (BA)

The `HttpBasicAuthenticationMiddleware` provides a method for the user to provide a username and password to access a resource, this should only be used with HTTPS.

```php
$autenticationMiddleware = new HttpBasicAuthenticationMiddleware(
    $container->get(IdentityServiceInterface::class), new BcryptPasswordHasher(), new ResponseFactory()
);
 ```

By default if the resource is protected and user has not authenticated, it will challenge the user for a username and password, if you prefer to throw an exception, then disable the challenge.

```php
$autenticationMiddleware->disableChallenge();
```

 ## Authentication Middleware Settings

All authentication middlewares have the following shared methods:

### Public paths

To make some urls public, and therefore dont require any authentication

```php
$middleware->setPublicPaths([
    '/status',
    '/about'
]);
```

### Area specific path

You can also lock down specific parts of your web application, such as an admin section.

```php
$middleware->setPath('/admin');
```
