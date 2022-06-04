# Template Renderer

To create a new `TemplateRenderer` object set the path of the templates folder and the path where the compiled templates can be saved.

## Usage

Create a template in your `views` folder

```php
<?php
/**
 * @var \Lightning\TemplateRenderer\TemplateRenderer $this
 */
?>
<h1>Heading <small class="text-muted">Secondary text</small></h1>
```

To create the `TemplateRenderer` object pass the template folder and temporary folder where the compiled templates are stored.

```php
$templateRenderer = new TemplateRenderer('/var/www/resources/views');
$output = $templateRenderer->render('articles/index');
```


You can also pass variables to the `TemplateRenderer`

```php
$templateRenderer->render('articles/index', ['foo' => 'bar']); // 
```


To render a template using a specific file, make sure the template name starts with `/`

```php
$output = $templateRenderer->render('/var/www/resources/views/articles/index.php');
```

By default the `TemplateRenderer` caches the template compliation to the system temp directory, however if you to prefer to use somewhere else, then during the creation of the object set the cache path.

```php
$templateRenderer = new TemplateRenderer('/var/www/resources/views', '/var/www/tmp/views');
```


## Layouts

Create a template that you want to use as a layout, and make sure to echo the `content` variable.

```php
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KyZXEAg3QhqLMpG8r+8fhAXLRk2vvoC2f3B09zVXn8CA5QIVfZOJ3BCsw2P0p/We" crossorigin="anonymous">
    <title>Web Application</title>
  </head>
  <body>
    <?= $content ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-U1DAWAznBHeqEIlVSCgzq+c9gqGAJn5c/t99JyeKa9xxaYpSvHU5awsuZVVFIhvj" crossorigin="anonymous"></script>
  
  </body>
</html>
```

Now configure the `TemplateRenderer` to use the layout

```php
$output = $templateRenderer
    ->withLayout('layouts/app') // /var/www/resources/views/layouts/app.php
    ->render('articles/index');
```

Or you can set from within the template

```php
<?php $this->layout = 'layouts/books'; ?> 
<h1>{{ $book->author }}</h1>
```

## Partials

You can also render a partial template (without a layout) from within the template

```php
<h1>{{ $book->author }}</h1>
<?= $this->render('partials/book', ['book' => $book]) ?>
```

## Security

You should pass all variables through the double curly brackets which will escape the output, to protect your application against against vulnerabilities such as XSS.

```php
<h1>{{ $book->author }}</h1>
```

## TemplateRenderer Attributes

You can set attributes which are then accessible inside the template 

```php
$templateRenderer->setAttribute('t', new Translator());
```

Then inside your `TemplateRenderer`

```php
<h1>{{ $t->translate('Users Home') }}</h1>
```