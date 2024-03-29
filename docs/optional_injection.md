Optional injection
------------------

Probably you do not want to inject an object every time, because the class will
work fine without the dependency. If a parameter has a default value and is
optional injection will be done using the default value.

```php
class BMWWithCoDriver extends BMW {
    public function __construct(
        private Engine $engine,
        private Tire $tire,
        private Person $driver,
        private ?Person $coDriver = null
    ) { }

    public function moveForward($miles) {
        if (null !== $this->codriver) {
            $this->codriver->sayHello();
        }

        parent::moveForward($miles);
    }
}
```

_stubbles/ioc_ does not support setter injection, therefore optional values must
be passed via the constructor as well.
