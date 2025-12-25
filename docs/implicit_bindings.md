Implicit bindings
-----------------

_stubbles/ioc_ does not force you to use interfaces in your type hints. If you are
already using concrete classes, there is no need to bind them, as _stubbles/ioc_
will implicitly bind the concrete class to itself:

```php
class Window {}

class House {
    private $window;

    public function __construct(Window $window) {
        $this->window = $window;
    }
}
```

When creating an instance of `House`, it will automatically have a reference to an
instance of `Window` although no special binding has been added. This also works
when `Window` itself has dependencies to other classes or constants.

_Please note that implicit bindings turn into explicit bindings once and injection
takes place or one of these methods is called:_

 * `stubbles\ioc\Binder::hasBinding()`
 * `stubbles\ioc\Injector::hasBinding()`
 * `stubbles\ioc\Injector::getInstance()`
