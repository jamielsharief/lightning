# Translator

The `Translator` component provides an object that can be passed around your application to translate messages into different languages.

## Usage

Create the `Translator` object and add to your DI container and configure the object in your `Middleware` or `Controller`

```php
$translator = new Translator(new PoMessageLoader('/var/www/resources/messages','/var/tmp/cached/messages'), 'en_US', 'default');
```

Usage

```php
$message = $translator->translate('Hello {user}!', ['user'=> $user->name]); // Hallo Jim
```

To change the locale run the folloing method, if you try to set a `locale` which does not exist, then it will use the `default` locale which was set when creating the `Translator` object.

```php
$translator->setLocale('en_GB');
```

The translator provides a locale fallback. So if `es_MX` is not available and there are translation files for `es` using the primary language then these will be loaded. This is quite practical since most applications just have main languages rather than one for each region. 


When you created the `Translator` object you either provided a domain or used the `default` one, domains allow you to work with multiple
language files in the same locale.

```php
$translator->setDomain('invoices'); // invoices.es_ES.po
```

The translator will always return a message, if no message is found it will return the original message sent.

## Message Loaders

The `MessageLoader` object file naming is `domain.locale.extension` for example `application.es_ES.po`

### PO Message Loader

A simple `PO` file loader which only supports basic syntax `msgid`, `msgstr` and string wrapping. This does not support pluralization, as this is designed to work with other message formatters which might have their own pluralization features such as ICU message syntax or our own `MessageFormatter`.

```php
# Comments and empty lines are ignored.
msgid "This is a translation test."
msgstr "Esta es una prueba de traducción."

# String wrapping
msgid ""
"This is an example of a really long line of text,"
" that will be translated into another language."
msgstr ""
"Este es un ejemplo de una línea de texto realmente larga,"
" que será traducida a otro idioma."
```

### PHP Message Loader

The PHP message files should return an array

```php
return [
    'Hello world!' => '¡Hola Mundo!',
    'You have {count} messages' => 'Tienes {count} mensaje(s)'
];
```

## Translator Middlewares

## Locale Setter

The `LocaleSetterMiddleware` quite simply sets the locale on the `Translator` using if the server request attribute `locale` is available. This
allows you to use this when routing, where you want to take the locale from the url e.g. `/blog/en/some-post` or if you want to detect from the request headers or even maybe the session.

```php
$translator = $container->get(Translator::class);
$middleware = new LocaleSetterMiddleware($translator); 
```

## Locale Detector

The `LocaleDetector` attempts to detect the locale from the request headers and will set the `locale` attribute on the `ServerRequestInterface` object.

```php
$middleware = new LocaleDetectorMiddleware('en_US');  // provide a default locale
```

You can also supply a second argument of allowed locales

```php
$middleware = new LocaleDetectorMiddleware('en_US', ['en_US','en_GB','es_MX','es_ES']); 
```
