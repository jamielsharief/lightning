# View

To create a new View object set the path of the views folder and the path where the compiled views will be saved.

## Usage

Create a `View` in your `views` folder

```php
<?php
/**
 * @var \Lightning\View\View $this
 */
?>
<h1>Heading <small class="text-muted">Secondary text</small></h1>
```

To create a `View`, create the `ViewCompiler` first.

```php
$compiler = ViewCompiler(
    '/var/www/resources/views', 
    '/var/www/tmp/views'
);
```

Now create the `View` object

```php
$view =  View(
    $compiler,
    '/var/www/resources/views'
);

$output = $view->render('articles/index');
```


You can also pass variables to the `View`

```php
$view->render('articles/index',['foo' => 'bar']); // 
```


To render a view using a specific file, make sure the view name starts with `/`

```php
$output = $view->render('/var/www/resources/views/articles/index.php');
```

## Layouts

To setup a layout

```php
$output = $view
    ->withLayout('app') // /var/www/resources/views/layouts/app.php
    ->render('articles/index');
```

Or you can set from within the view

```php
<?php $this->layout = 'books'; ?> 
<h1>{{ $book->author }}</h1>
```

## Partials

You can also render a partial view (without a layout) from within the view

```php
<h1>{{ $book->author }}</h1>
<?= $this->render('partials/book', ['book' => $book]) ?>
```

## Security

You should pass all variables through the double curly brackets which will escape the output, to protect your application against against vulnerabilities such as XSS.

```php
<h1>{{ $book->author }}</h1>
```

## View Attributes

You can set attributes which are then accessible inside the `View`, if a 

```php
$view->setAttribute('t', new Translator());
```

Then inside your `View`

```php
<h1>{{ $t->translate('Users Home') }}</h1>
```