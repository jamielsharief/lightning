# PSR-4 Autoloader

A lightweight [PSR-4](https://www.php-fig.org/psr/psr-4/) Autoloader.

## Usage

Create the `Autoloader` instance pointing to the project root and then add the namespaces which you want it to resolve then call `register`.

```php
$autoloader = new Autoloader(dirname(__DIR__));
$autoloader->addNamespaces([
    'App' => 'app',
    'Lightning' => 'src',
    'Lightning\\Test' => 'tests'
]);
$autoloader->register();
```