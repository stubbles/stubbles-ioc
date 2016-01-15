Closure bindings
----------------

_Available since release 2.1.0._

It might be useful to initialize some values lazy, but using an [injection
provider](injection_providers.md) would be overblown. This is where closure
bindings come in handy:

```php
$binder->bind('Person')->toClosure(function() { return new Schst(); });
// other bindings

$injector = $binder->getInjector();
$bmw = $injector->getInstance('Car');

var_dump($schst);
var_dump($bmw);
```

The code inside the closure has to create the value, it doesn't have any access
to the injector, so if there any dependencies they must be available at the
moment the closure is created.

For class bindings the closure binding can be combined with scopes, i.e. the
[singleton scope](singleton_scope.md):

```php
    $binder->bind('Person')->toClosure(function() { return new Schst(); })->asSingleton();
```

Closure bindings are also available for [constant bindings](constant_values.md):

```php
    $binder->bindConstant('answer')->toClosure(function() { return 42; });
```
