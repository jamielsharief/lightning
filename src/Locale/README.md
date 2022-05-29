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

The `ResourceBundle` handles the loading and retriving the key values from a `php` (default) or `json` file.

To create the `ResourceBundle`

```php
$bundle = new ResourceBundle('/var/www/resources', new Locale('en_GB'), 'messages'); // /var/www/resources/messages.en_GB.php
```

Then create the file e.g. `/var/www/resources/messages.en_GB.php`

```php
<?php
return [
    'hello_world' => 'Hello, World!'
];
```

If you prefer to work a different file format then you can create your own custom `ResourceBundle` and overwrite the `loadContents` method, for example:

```php
class JsonResourceBundle extends ResourceBundle
{
    protected function loadContents(): array
    {
        $path = $this->getResourceBundlePath('json'); // e.g /var/www/resources/messages.en_GB.json
        if (! file_exists($path)) {
            throw new ResourceNotFoundException(sprintf('Resource bundle `%s` cannot be found', basename($path)));
        }

        return json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    }
}
```

To work with the `ResourceBundle`

```php
$string = $bundle->get('hello_world');
$bundle->has('hello_world');

// Changing the name or locale will load the messages again
$bundle->setLocale(new Locale('es_ES')); // /var/www/resources/messages.es_ES.yaml
$bundle->setName('invoice_plugin'); // /var/www/resources/invoice_plugin.en_GB.yaml

// Get a new isntance with a different locale or a different resource
$bundle = $bundle->withLocale(new Locale('es_ES')); 
$bundle = $bundle->withName('invoice_plugin');
```
