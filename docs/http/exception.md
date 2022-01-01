# Http Exception

A set of ready made reusuable Exceptions for HTTP applications


Create any HTTP exception

```php
throw new HttpException('Proxy Authentication Required', 407);
```

Use on of the ready made

```php
throw new BadRequest();
throw new BadRequest('You need to supply the API token');
```