# Hooks

Hooks package allows you to modify or extend the behavior of an object using the [Interceptor pattern](https://en.wikipedia.org/wiki/Interceptor_pattern).

## Usage

Register a hook to a method or multiple methods, by default all hooks can cancel the behavior that is being hooked into. 

The `triggerHook` method always returns true even if there are no hooks registered, it only returns false if the hook was stoppable and the method returned `false`.

```php
class MyObject
{
    use HookTrait; 

    public function __construct()
    {
        $this->registerHook('beforeFind','doSomething');
    }

    protected function doSomething(array $criteria)
    {
        return !empty($criteria);
    }

    public function find(array $criteria) : bool 
    {
        if(!$this->triggerHook('beforeFind',[$criteria])){
            return false;
        }
        $result = $this->dataSource->find($criteria);

        //.. do something
        $this->triggerHook('afterFind',[$result, $criteria], false);
    }
}
```

Differences between `Hook` and  `Event`, hooks are only triggered on the object itself, as opposed to any callable and there is a standard way to register a hook, making it more extendable.