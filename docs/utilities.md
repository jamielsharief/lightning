# Utilities

A collection of utility components

## RandomString

A secure random string generator, with various characterset constants (hex,base36,base58, base62,base64,base64 url safe)

```php
$randomString = new RandomString();

$randomString->generate(12); // 7nH3XfBYZG5E

$randomString
    ->withCharset(Randomstring::HEX)
    ->generate(32); // 9cee331c6104f9035e57259ec13f7d98

$randomString
    ->withCharset('foobar')
    ->generate(8); // aaborrro

$randomString
    ->withCharset(RandomString::BASE_62 . RandomString::SPECIAL)
    ->generate(32); // 86^PY)f$T06x#KJO

$randomString
    ->withCharset(Randomstring::BASE_64_URL_SAFE)
    ->generate(24); //Go6-dQapORAGBkmY1UQ0eT53
```

## UUID

A simple RFC-4122 compliant UUID v4 generator

```php
$uuid = (new Uuid())->generate(); // 57519f4b-7f7e-4ceb-ae80-a139dc6f07e9
```

There is also a preconfigured constant for matching regular expressions

```php
$this->assertMatchesRegularExpression(Uuid::PATTERN, $uuid);
```