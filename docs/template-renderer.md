# Template Renderer

To create a new TemplateRenderer object set the path of the views folder and the path where the compiled views will be saved.

## Usage

Create a `TemplateRenderer` in your `views` folder

```php
<?php
/**
 * @var \Lightning\TemplateRenderer\TemplateRenderer $this
 */
?>
<h1>Heading <small class="text-muted">Secondary text</small></h1>
```

To create the `TemplateRenderer` pass the template folder and temporary folder where the compiled templates are stored.

```php
$templateRenderer =  TemplateRenderer('/var/www/resources/views', '/var/www/tmp/views');
$output = $templateRenderer->render('articles/index');
```


You can also pass variables to the `TemplateRenderer`

```php
$templateRenderer->render('articles/index',['foo' => 'bar']); // 
```


To render a view using a specific file, make sure the view name starts with `/`

```php
$output = $templateRenderer->render('/var/www/resources/views/articles/index.php');
```

## Layouts

To setup a layout

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

You can set attributes which are then accessible inside the `TemplateRenderer`, if a 

```php
$templateRenderer->setAttribute('t', new Translator());
```

Then inside your `TemplateRenderer`

```php
<h1>{{ $t->translate('Users Home') }}</h1>
```