Named parameters
----------------

For the next example, we will modify the used classes a little bit:

 1. Add a new class `Mikey` which implements the `Person` interface
 2. Add a new property `$coDriver` and the matching constructor parameter to the `BMW` class

This is the new code:

```php
class BMW implements Car {
    public function __construct(
        private Engine $engine,
        private Tire $tire,
        private Person $driver,
        private ?Person $coDriver = null
    ) { }
}

class Mikey implements Person {
    public function sayHello(): string {
        echo "My name is Frank\n";
    }
}
// existing classes left out
```

If you create an instance of `BMW` with this changed code, you will get the
following object structure:

```
object(BMW)#35 (4) {
  ["driver:private"]=>
  object(Schst)#51 (0) {
  }
  ["coDriver:private"]=>
  object(Schst)#51 (0) {
  }
  ["engine:private"]=>
  object(TwoLitresEngine)#39 (0) {
  }
  ["tire:private"]=>
  object(Goodyear)#42 (0) {
  }
}
```

The properties `$driver` and `$coDriver` both contain references to the instance
of `Schst` as both parameters are typehinted against the `Person` interface. In
real-life, you would probably be able to inject a different co-driver, but until
now, an interface can only be bound to one implementation. This can be changed
using the `@Named` annotation.

Again, you need to modify the `BMW` class a little bit:

```php
class BMW implements Car {
    /**
     * @Named{coDriver}('Co-Driver')
     */
    public function __construct(
        private Engine $engine,
        private Tire $tire,
        private Person $driver,
        private ?Person $coDriver = null
    ) { }
}
```

By adding the `@Named{coDriver}('Co-Driver')` annotation, you gave _stubbles/ioc_
the possibility to distinguish the `Person` instance passed to `setCoDriver`
from all other `Person` instances. You may now specify a separate binding for
this instance:

```php
$binder->bind(Person::class)->to(Schst::class);
$binder->bind(Person::class)->named('Co-Driver')->to(Mikey::class);
// other bindings

$injector = $binder->getInjector();
$bmw = $injector->getInstance(Car::class);
var_dump($bmw);
```

Now, the `$injector` will return the following object structure:

```
object(BMW)#34 (4) {
  ["driver:private"]=>
  object(Schst)#50 (0) {
  }
  ["coDriver:private"]=>
  object(Mikey)#57 (0) {
  }
  ["engine:private"]=>
  object(TwoLitresEngine)#38 (0) {
  }
  ["tire:private"]=>
  object(Goodyear)#41 (0) {
  }
}
```

As desired, _stubbles/ioc_ created an instance of the new class `Mikey` and
injected it using the `setCoDriver()` method. You may use as many named bindings
for one type as you like and combine it with all other features like scoping.

The `@Named` annotation can be used in several ways:

 * per method, binding all parameters of the method to this name
 * per parameter, binding only the parameter to this name

Please note that a named parameter will overwrite a named method for this
parameter, but that you can not overwrite a parameter with the default non-named
behaviour if the method is already bound to the name.

