# Params Object

The `Params` object is for passing parameters and working a set of parameters to an object which can be used, contracted etc.  

If `get` is called and the parameter was not supplied it will throw `UnkownParameterException`, therefore, for optional parameters check with `has` first.

```php
$params = new Params(['name' => 'fred', 'email' => 'fred@example.com']);
$name = $params->get('name');
$bool = $params->has('surname');
```
