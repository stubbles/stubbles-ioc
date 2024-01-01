Inject instances
----------------

In some cases you might need to inject a dependency that is not managed by
_stubbles/ioc_, but created by your own code. Instead of binding a type to a
concrete implementation, you can always bind it to an existing instance. In the
following example you already have an instance of the class `Schst` created and
you want to inject this into the `BMW` instance, instead of letting the injector
create a new instance:

```php
$schst = new Schst();

$binder->bind(Person::class)->toInstance($schst);
// other bindings

$injector = $binder->getInjector();
$bmw = $injector->getInstance(Car::class);

var_dump($schst);
var_dump($bmw);
```

Instead of using the `to()` method to specify the binding, you only need to call
`toInstance()` and pass the object to use for the binding. The result of this
script is:

```
object(Schst)#14 (0) {
}
object(BMW)#38 (4) {
  ["driver:private"]=>
  object(Schst)#14 (0) {
  }
  ["engine:private"]=>
  object(TwoLitresEngine)#42 (0) {
  }
  ["tire:private"]=>
  object(Goodyear)#45 (0) {
  }
}
```

As you can see, the `BMW` instance contains a reference to the `Schst` instance
you created, there object handle is _#14_. Please note that this type of binding
acts similarly as binding a class into the [singleton scope](singleton_scope.md).
