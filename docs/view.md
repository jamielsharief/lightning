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

## Partials

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

## View Extensions

You can extend view functionality by creating an `Extension`, then the methods will be available in the `View`.

```php
use Lightning\View\ViewExtensionInterface;

class DateExtension implements ViewExtensionInterface
{
    public function getMethods() : array 
    {
        return [
            'format'
        ];
    }

    protected function format(string $value) : string 
    {
        return date('d/m/Y H:i:s', strtotime($value));
    }
}
```

When you create the `View` object

```php
$view->addExtension(new DateExtension);
```

Then in your view file you can use it using `this`

```php
<span><?= $this->format($date) ?></span>
```

## View Helpers

For more complex functionality you can add any helper object

```php
class DateHelper implements ViewHelperInterface
{
    public function getName() : string 
    {
        return 'Date';
    }

    public function format(string $value) : string 
    {
        return date('d/m/Y H:i:s', strtotime($value));
    }
}
```

When you create the `View` object

```php
$view->addHelper(new DateHelper());
```

Then in your view file you can use it using `this`

```php
<span><?= $this->Date->format($date) ?></span>
```

You can then also add docblock comments like this in your application view which will help with autocompletion when
working with views.

```php
namespace App\View;
use Lightning\View\View as BaseView;

/**
 * @property \App\View\Helper\PaginatorHelper $Paginator Paginator Helper
 * @method int sum() sum(int $a, int $b)  Sum extension 
 */
class ApplicationView extends BaseView
{
    protected function initialize() : void
    {
    }
}
```
