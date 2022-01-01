# Translation

The translation component provides an object that can be passed around your application to translate messages into different languages.

## Usage

Create the `Translator` object and add to your DI container and configure the object in your `Middleware` or `Controller`

```php
$translator = new Translator(new PoMessageLoader('/var/www/resources/messages'),'en_US','default');
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

A simple `PO` file loader which only supports basic syntax `msgid`, `msgstr` and string wrapping. The message loader also supports `PSR-16` caching for parsing of files. This does not support pluralization, as this is designed to work with other message formatters which might have their own pluralization features such as ICU message syntax or our own `MessageFormatter`.

```php
# Comments and empty lines are ignored.
msgid "This is a translation test."
msgstr "Esta es una prueba de traducción."
```

### PHP Message Loader

The PHP message files should return an array

```php
return [
    'Hello world!' => '¡Hola Mundo!',
    'You have %s messages' => 'Tienes %s mensaje(s)'
];
```

## TranslationMiddleware

There is `TranslationMiddleware` which gets the `Accept-Language` from the request and configures the `Translator` object. You can also set the `locale` cookie and this will be used if found.

```php
$translator = $container->get(Translator::class);
$middleware = new TranslationMiddleware($translator); 
```

If you want to restrict the locales that are available to the middleware

```php
$middleware = new TranslationMiddleware($translator, ['en_GB','nl_NL']); 
```
