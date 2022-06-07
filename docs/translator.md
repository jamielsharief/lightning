# Translator

The `Translator` component provides an object that can be passed around your application to translate messages into different languages using the ICU message formatter. It also supports simple message formatting.

## Usage

Create the `Translator` object and add to your DI container and configure the object in your `Middleware` or `Controller`. 

```php
$bundleFactory = new ResourceBundleFactory(__DIR__ . '/resources/app'); 
$translator = new Translator($bundleFactory, 'en_US');
```

Create your translation file

```php
return [
    'Hello {user}!' => '¡Hola {user}!'
    'Hello world!' => '¡Hola Mundo!',
    'You have {count} messages' => 'Tienes {count} mensaje(s)'
];
```

The `Translator` uses ICU message format, but also provides a custom pluralazation engine if you dont want or need to use the ICU message format syntax.

To translate a message

```php
$message = $translator->translate('Hello {user}!', ['user'=> $user->name]); // Hallo Jim
```

> Many languages only have two plural forms such as English, then Chinese and Japanses only has one plural form, slavic langauges have 3 or more forms and arabic and a few other languages have 6 or more.

If you are not using the ICU message format syntax, you can provide the different plural forms and seeperating with a `|`, and pass a key called `count`. When the string is split, if the index is found for the count it will use that, if not it will use the last index. 

```php
$message = $translator->translate('There are zero apples|There is 1 apple|There are {count} apples', ['count'=> count($apples)]); 
```

To change the locale run the folloing method, if you try to set a `locale` which does not exist, then it will use the `default` locale which was set when creating the `Translator` object.

```php
$translator->setLocale('en_GB');
```

You can also get a new instance of the `Translator` object with a different locale.

```php
$spanishTranslator = $translator->withLocale('es_ES');
```

The translator will always return a message, if no message is found it will return the original message sent.

## Translator Middlewares

The translator middlewares help configuring the `Translator` object per request.

## Locale Detector

The `LocaleDetector` attempts to detect the locale from the request headers and will set the `locale` attribute on the PSR 7 server request object.

```php
$middleware = new LocaleDetectorMiddleware('en_US');  // provide a default locale
```

You can also supply a second argument of allowed locales

```php
$middleware = new LocaleDetectorMiddleware('en_US', ['en_US','en_GB','es_MX','es_ES']); 
```

## Locale Setter

The `LocaleSetterMiddleware` quite simply sets the locale on the `Translator` using if the PSR 7 server request object has the  `locale` attribute  set. This allows you to use this when routing, where you want to take the locale from the url e.g. `/blog/en/some-post` or if you want to detect from the request headers or even maybe the session.

```php
$translator = $container->get(TranslatorInterface::class);
$middleware = new LocaleSetterMiddleware($translator); 
```

## Translate Function

For those who can't live without the `__` function, set the `Translator` object in the `TranslatorManager` then call the funtion.

```php
use function Lightning\Translator\__;

TranslatorManager::set($translator);

echo __('Hello world!');
```