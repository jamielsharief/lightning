# PSR-15 Exception Handler

The [PSR-15](https://www.php-fig.org/psr/psr-15/) recommends that exceptions are caught and returned as a `Response` using a middleware component which is executed at the start.

## Usage

Create the Middleware by passing a path to the error files , the ErrorRenderer object, a `PSR-17 Response Factory` and optionally a `PSR-3 Logger` and add this as the **first** middleware to the stack.

```php
$middleware = new ExceptionHandlerMiddleware(
        __DIR__ . '/../app/View/error' , new ErrorRenderer(), new ResponseFactory(), new Logger()
    );
```

Any `Lighting\Http\Exceptions\HttpException` exceptions will show the exception message and status code that was passed, all other exceptions will be treated as an internal server error with the generic message `Internal Server Error`. HTTP exceptions are designed to be showed to the user, whilst other exception messages are not.

### HTML Rendering

You must have two files `error400.php` or `error500.php` will be rendered, `code`,`message`,`request` and `exception` vars will be passed to these. See below for a template.


### JSON 

If the request header accepts `application/json` then the exception will be rendered as JSON.

```json
{
  "error": {
    "code": 500,
    "message": "Internal Server Error"
  }
}
```

### XML 

If the request header accepts `text/xml` or `application/xml` then the exception will be rendered as XML.

```xml
<?xml version="1.0" encoding="UTF-8"?>
<error>
   <code>500</code>
   <message>Internal Server Error</message>
</error>
```

## Error Handling

There is also a simple `ErrorHandler` class that will convert all errors to exceptions which you can add to your bootstrap process.

```php
error_reporting(E_ALL);
(new ErrorHandler())->register();
```

## Example Template

Here is an example for `error400.php`

```
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KyZXEAg3QhqLMpG8r+8fhAXLRk2vvoC2f3B09zVXn8CA5QIVfZOJ3BCsw2P0p/We" crossorigin="anonymous">
  <title><?= $code ?> - <?= $message ?></title>
  <style>
    html,
    body {
      height: 100%
    }
    body {
      margin: 0;
      padding: 20px;
      background-color: #FFC900;
    }
    .block span,
    .block p {
      font-weight: 700;
      color: #fff;
    }
  </style>
</head>
<body>
  <div class="h-100 row align-items-center">
    <div class="block col-md-12 text-center">
      <span class="display-1 d-block"><?= $code ?></span>
      <div class="mb-4 lead">
        <p><?= $message ?></p>
      </div>
    </div>
  </div>
</body>
</html>
```

## Resources

- https://www.php-fig.org/psr/psr-3/
- https://www.php-fig.org/psr/psr-15/
- https://www.php-fig.org/psr/psr-17/