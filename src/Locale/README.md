# Locale 

The Locale package provides the `Locale` and `ResourceBundle` object.

## Locale

The `Locale` object is designed to manage the locale for application and can be passed around easily.

```php
$locale = new Locale('en_GB'); 

// getters and setters for locale
$locale->get(); // en_GB
$locale->set('es_MX');

// set and get the default locale
$locale->setDefault('en_US');
$locale->getDefault();

// set and get the available locales (setters and getters check this)
$locale->setAvailable([
    'en_GB',
    'en_US'
]);
$available = $locale->getAvailable();
```

There are also some helper functions

```php
$locale = new Locale('es_ES'); 

$locale->getLanguage(); // es

$locale->getDisplayRegion(); // Spain
$locale->getDisplayRegion('es_ES'); // España

$locale->getDisplayLanguage(); // Spanish
$locale->getDisplayLanguage('es_ES'); // español

$locale->getDisplayName(); // Spanish (Spain)
$locale->getDisplayName('es_ES'); // español (España)
```

## Resource Bundle

The `ResourceBundle` handles the loading and retriving the key/value dictonary.

To create the `ResourceBundle` call the factory method `create`.

```php
$bundle = ResourceBundle::create(new Locale('en_US'), __DIR__ . '/resources/app'); // /var/www/resources/app/en_GB.php
```

Then create the file e.g. `/var/www/resources/app/en_GB.php`

```php
<?php
return [
    'hello_world' => 'Hello, World!'
];
```

If you prefer to work a different file format then you can create your own custom `ResourceBundle` and overwrite the factory method `create`, for example if you wanted to work with `json` files instead.

```php
class JsonResourceBundle extends ResourceBundle
{
    public static function create(Locale $locale, string $bundle): static
    {
        $path = sprintf('%s/%s.json', $bundle, $locale->toString());
        if (! file_exists($path)) {
            throw new ResourceNotFoundException(sprintf('Resource bundle `%s` cannot be found', basename($path)));
        }

        return new static(
            $locale, 
            json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR)
        );
    }
}
```

To work with the `ResourceBundle`

```php
$bundle->has('hello_world'); // check if key exists
$string = $bundle->get('hello_world'); // throws exception if key does not exist
```
