# LightningPHP (beta)

![license](https://img.shields.io/badge/license-MIT-brightGreen.svg)
[![build](https://github.com/jamielsharief/lightning/workflows/CI/badge.svg)](https://github.com/jamielsharief/lightning/actions)
[![coverage](https://coveralls.io/repos/github/jamielsharief/lightning/badge.svg?branch=master)](https://coveralls.io/github/jamielsharief/lightning?branch=master)

A set of lightweight components that can be used together or seperatley. 

- [ ] Fast
- [ ] Low memory usage
- [ ] Secure
- [ ] Essential
- [ ] PSR Standards
- [ ] Any learning should transportable
- [ ] Linux based OS (sorry Windoz)

Documentation can be found in the [Docs Folder](docs/) 

## Setup

Create an `.env` file in the root directory, 

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
- This is suppose to be building blocks, e.g PSR-Events inside a controller should be implemented in a seperate controller overiding the exsting controller

## Conventions

### Names
- use Abstract, Interface, Trait to identify
- use a standard `toString` instead of `toJson`, to prevent duplicate code. 
- rather than `getState` use `toArray`, this prevents clashing with user defined value objects which have their own getters and setters
- setters and getters, set,Get. If setter does not return a bool, instead of void return the object.
- accesibility, main methods on object should be public even if they are not supposed to be called from outside. e.g. controller render, console command output.
- GET should be used for single items


To think about
- interfaces, use getEventName or eventName(), getSubscribedEvents() or subscribedEvents();
- methods should be public even if they were not suppose to be called from the outside, e.g. Controller::render, only functions it calls can then be hidden.
