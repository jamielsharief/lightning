# LightningPHP (alpha)

![license](https://img.shields.io/badge/license-MIT-brightGreen.svg)
[![build](https://github.com/jamielsharief/lightning/workflows/CI/badge.svg)](https://github.com/jamielsharief/lightning/actions)
[![coverage](https://coveralls.io/repos/github/jamielsharief/lightning/badge.svg?branch=master)](https://coveralls.io/github/jamielsharief/lightning?branch=master)

> Currently under development and desgins are subject to change.

A set of lightweight components that can be used together or seperatley. 

- [ ] Fast
- [ ] Low memory usage
- [ ] Secure
- [ ] Essential
- [ ] PSR Standards
- [ ] Any learning should transportable
- [ ] Linux based OS (sorry Windoz)

## Setup

All components are in the `src` folder, the `app` folder, is just me playing around with the usage.

- create an `.env` file in the root directory, 

```php
$ cp .env.example .env
$ docker compose build
$ docker compose up
$ docker compose exec app bash
```

Then access the database from your desktop using `127.0.0.1`, from inside docker the database host is `mysql`.

Create the `lightning` database and import `database/schema/schema.sql`

Then you can run tests from within docker

```php
$ vendor/bin/phpunit
```

## Notes

- Factory method should be `create` or `createObject`
- Minimum requirement of PHP 8 will be only set once its in Ubuntu server main repo, this gives ample time to ensure thats its available.

## TODO

- Make sure exceptions extend runtime exception, not exception unless there is reason too.

This repo will be split using [https://www.subtreesplit.com/](https://www.subtreesplit.com/), great free service by @Nyholm


- Ideally components should work with POPO in an easy way.

- Router should have main exceptions NotFound, BadRequest, Not Authorized, Forbidden, should be seperated from the framework. Middleware to intercept perhaps? HttpException 

- Simplify router, passed arguments should be added to request and passed as second param. Remove autocasting. For constriants remove the leading and trailing slashses. / 

- Response Emititng seperate
- Move cookies to own package

## Conventions

### Names
- use Abstract, Interface, Trait to identify
- use a standard `toString` instead of `toJson`, to prevent duplicate code. 
- rather than `getState` use `toArray`, this prevents clashing with user defined value objects which have their own getters and setters
- setters and getters, set,Get. If setter does not return a bool, instead of void return the object.

- accesibility, main methods on object should be public even if they are not supposed to be called from outside. e.g. controller render, console command output.

## Thoughts

- Controller should be simpler, logic such as authorization should all be in the middleware me thinks.

```php 
class ArticlesController
{
    private View $view;
    private Session $session;

    public function __construct(View $view, Session $session) 
    {
        $this->view = $view;
        $this->session = $session;

        $this->initialize();
    }

    public function view(ServerRequestInterface $request) : ResponseInterface
    {
        throw new NotImplementedException; // 501
    }

    public function index(ServerRequestInterface $request) : ResponseInterface
    {
        $body = $this->view->render('articles/index');

        // throw new NotFoundException();

        $response = new Response();
        $response->getBody()->write($body);
        return $response->withStatus(200);
    }
}
```

Now can remove method autowiring