Singletons
----------

Multiple calls to `$injector->getInstance(Car::class);` will return different
objects. In most cases, this is probably what you want, as the IoC framework
behaves like the `new` operator. If you want to create only one instance of the
`BMW` class, you can easily convert the `BMW` class to a [singleton](http://en.wikipedia.org/wiki/Singleton_pattern).

```php
$binder = new stubbles\ioc\Binder();
$binder->bind(Car::class)->to(BMW::class)->asSingleton();
// other bindings

$injector = $binder->getInjector();
$bmw1 = $injector->getInstance(Car::class);
$bmw2 = $injector->getInstance(Car::class);

if ($bmw1 === $bmw2) {
    echo "Same object.\n";
}
```

Using `asSingleton()` makes sure that the instance is created only once and
subsequent calls to `getInstance()` will return the same instance.

Another way to treat a class as a singleton is using the `#[Singleton]`
attribute. The following example makes sure that the application uses
only one instance of the class `Schst`:

```php
use stubbles\ioc\attributes\Singleton;

#[Singleton]
class Schst implements Person {
    public function sayHello(): string {
        echo "My name is Stephan\n";
    }
}
```

The following code will now create two instances of the class `BMW`, but both
should have a reference to the same `Schst` instance:

```php
$binder = new stubbles\ioc\Binder();
$binder->bind(Car::class)->to(BMW::class);
// other bindings

$injector = $binder->getInjector();
$bmw1 = $injector->getInstance(Car::class);
$bmw2 = $injector->getInstance(Car::class);

var_dump($bmw1);
var_dump($bmw1);
```

If you run the code snippet, you get the following output:
```
object(BMW)#34 (3) {
  ["driver:private"]=>
  object(Schst)#50 (0) {
  }
  ["engine:private"]=>
  object(TwoLitresEngine)#38 (0) {
  }
  ["tire:private"]=>
  object(Goodyear)#41 (0) {
  }
}
object(BMW)#30 (3) {
  ["driver:private"]=>
  object(Schst)#50 (0) {
  }
  ["engine:private"]=>
  object(TwoLitresEngine)#44 (0) {
  }
  ["tire:private"]=>
  object(Goodyear)#39 (0) {
  }
}
```

As you can see, the two `BMW` instances have different object handles (_#30_
and _#34_), but the `$driver` properties point to the same `Schst` instance
(object handle _#50_).
