# Dotenv

A ultra simple and lightweight Dotenv parser.

- empty lines will be skipped
- lines that start with # are ignored
- lines should be NAME=value or NAME="some value with space", but no spaces before the var name, and no spaces before or after the equal sign. No cleansing is done.

## Usage

In your bootstrap file create the object with the directory that you want to use and load this

```php
// Load .env in the working directory
(new Dotenv(dirname(__DIR__)))->load();
```

An example `.env` file will look like this, key values should be written correctly `KEY=value` with no spaces. No comments should be added
after values.

```
# This is a comment line
KEY=value
```

## Env function

This package also comes with an `env` function.

```php
$value = env('key');
```
