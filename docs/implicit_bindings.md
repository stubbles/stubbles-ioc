Implicit bindings
-----------------

_stubbles/ioc_ does not force you to use interfaces in your type hints. If you are
already using concrete classes, there is no need to bind them, as _stubbles/ioc_
will implicitly bind the concrete class to itself:

```php
class Window {}

class BMW implements Car {
    private $window;

    // same constructor and methods as in previous examples

    /**
     * @Inject
     */
    public function setWindow(Window $win) {
        $this->window = $win;
    }
}
```

When creating an instance of `BMW`, it will automatically have a reference to an
instance of `Window` although no special binding has been added.

_Please note that implicit bindings turn into explicit bindings once one of
these methods is called:_

 * `stubbles\ioc\Binder::hasBinding()`
 * `stubbles\ioc\Injector::hasBinding()`
 * `stubbles\ioc\Injector::getInstance()`
