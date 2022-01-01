# Formatter

## MessageFormatter

The `MessageFormatter` is a simple formater

```php
$formatter  = new MessageFormatter();
echo $formatter->format('Hello {name}',['name' => 'jon']);
```

You can also display messages depending up on the `count` argument supplied, you must always provide at least `3` variations, `zero`, `one` and `many`, if the count does not exist in the index, it will return the last message in the invoice.

```php
echo $formater->format('You have no invoices|You have one invoice|You have {count} invoices',['count' => 4]); // you have 4 invoices
echo $formater->format('You have no invoices|You have one invoice|You have two invoices| You have {count} invoices',['count' => 3]); // you have 3 invoices
```

## DateTimeFormatter

The `DateTimeFormatter` object gives you an application friendly way to work with dates and time. You can also configure this object
in your DI container and then change the settings for the object in the `Middleware` or `Controller` which will then change how your whole application formats dates for each user, depending upon their preferences.

```php
date_default_timezone_set('UTC');
```

Create a `DateTimeFormatter` object in your DI container making sure the same object is injected into dependencies, so that any changes made to the configurion are used elsewhere in the application.

```php
$formatter  = new DateTimeFormatter(); // UTC with `Y-m-d` for dates and  `H:i:s` for times or `Y-m-d H:i:s` for datetime
```

In your `Controller` you can configure the `DateTimeFormatter` object based upon the user preferences.

```php
$formatter->setTimezone('Europe/London');
$formatter->setDateTimeFormat('d/m/Y H:i:s');
$formatter->setTimeFormat('H:i:s');
$formatter->setDateFormat('d/m/Y');
```

To format a datetime using configured settings, including timezone:

```php
$now = new DateTime(); // or string, or unix timestamp or class that implements Stringable

// Use the configured default formats
$formatter->date($now);
$formatter->time($now);
$formatter->datetime($now);

// use a custom format
$formatter->format($now); // formats as datetime if no format provided
$formatter->format($now,'D, d M Y H:i:s O'); // use any DateTime format but still converting to user timezone

```

## NumberFormatter

The `NumberFormatter` object gives you an application friendly way to work with numbers. You can also configure this object
in your DI container and then change the settings for the object in the `Middleware` or `Controller` which will then change how your whole application formats numbers for each user, depending upon their preferences.

```php
$formatter = new NumberFormatter(); // Default currency USD and thousands symbol configured to , and decimals symbol to .
```

You can pass a `float` or `integer`, or a `string` representation of one of those. Passing a `null` value or an empty `string` will throw
an exception.

The format number will format all numbers without any decimal places unless you provide a second argument of places. The places argument is ignored
on values which are `integer` or `strings` only have integers in them and do not include the `.` decimal seperator.

```php
$formatter->format(123456.7890); // 123,456
$formatter->format(123456.7890,2); // 123,456.78
$formatter->format(123456,2); // 123,456 
$formatter->format((float) 1234, 2); // 1,234.00
```

To use precision

```php
$formatter->precision(123456.7890); // 123,456.789 - use default of 3
$formatter->precision(123456.7890,2); // 123,456.78
```

### Currencies

The following currencies have already been setup but can be overrided `USD`, `GBP`, `EUR`, `JPY` ,`CAD` and `CHF`

Like the `format` method, if you provide an `integer` value it wont add `.00` 

```php

$formatter->currency(123456.7890); // $123,456.78 - uses default currency USD and currency setting of 2
$formatter->currency(123456.7890, 'GBP'); // £123,456.78

// work with 00
$formatter->currency(123456, 'GBP'); // £123,456
$formatter->currency((float) 123456, 'GBP'); // £123,456.00

// working with negatives
$formatter->currency(-123456.7890); // (£123,456.78)

// passing a unkown currency
$formatter->currency(123456.7890, 'FOO'); // 123,456.78 FOO - this is unkown currency
```

To add a custom currency

```php
$formatter->addCurrency('BTC', '฿', '', 3);
$formatter->currency(0.1234567, 'BTC'); // ฿0.123
$formatter->currency(1234.5678, 'BTC'); // ฿1,234.568
```


# Percentages

By default percentages are formatted with a precision of 2 unless the number is an integer, to force the `.00` then make sure to always
pass a float.

```php
$formatter->toPercentage(99); // 99% 
$formatter->toPercentage(99.9999,0); // / 99%

$formatter->toPercentage(99.9999); // 99.99
$formatter->toPercentage((float) 99); // 99.00% 

$formatter->toPercentage(99.9999,3);  // 99.999 to use custom precision
```

# Readable Sizes

Convert bytes into human readable sizes, for sizes greater than `KB` precision will be used for non round numbers.

```php
$number->toReadableSize(0); // 0 B
$number->toReadableSize(1073741824); // 1,024 MB
```

To force precision

```php
$number->toReadableSize(0); // 0 B
```


## Todo

In future I plan to add these

- [ ] DateTimeFormatterInterface (just include setTimezone not others)
- [ ] NumberFormatterInterface maybe
- [ ] Add IcuDateTimeFormatter
- [ ] Add IcuNumberFormatter