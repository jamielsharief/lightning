# Autowire

You can create the `Autowire` class with or without a PSR-11 Container.

```php
$autowire = new Autowire()
$autowire = new Autowire($diContainer);
```

You can autowire a class, a method of an object or a closure

```php 
$object = $autowire->class(ArticlesController::class); 

$response = $autowire->method($object, 'index'); // you can also pass a 3rd argument for additional params

$result = $autowire->function(function(Session $session){
    return $session->get('foo');
});
```