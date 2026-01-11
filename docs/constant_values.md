Constant values
---------------

Until now, you used the Stubbles IoC framework only to inject objects. However,
it is also possible to inject constant values into objects. The following
example shows how this can be done. Imagine you have a class that requires the
connection parameters for a database:

```php
use stubbles\ioc\attributes\Named;
class MyApplication {

    public function __construct(#[Named('dbParams')] private string $params) { }

    // ... other methods of the class
}
```

As in the examples before, you the marked the parameter that should be used to
inject the database connection parameters with the `#[Named]` attribute. When
injecting non-objects, the `#[Named]` attribute is required, as there is no type
hint to identify the binding.

Now, all that's left to do is specify a binding for the constant `dbParams`:

```php
$binder->bindConstant('dbParams')->to('mysql:host=localhost;dbname=test');

$injector = $binder->getInjector();
$app = $injector->getInstance(MyApplication::class);
```

The `$injector` will now return a configured instance of `MyApplication` with
the `$dbParams` property set.

_stubbles/ioc_ is not meant to replace your own configuration framework, but in
some cases injecting constant values can be helpful or might even be a
requirement for your application to work.

Injecting constants works everywhere where the `#[Named]` attribute can be
applied.

It is also possible to retrieve single constant values directly from the binder:

```php
$dbParams = $injector->getConstant('dbParams');
```

Note: in order to prevent errors from typos, it is recommended to use constants
for the names:

```php
use stubbles\ioc\attributes\Named;
class MyApplication {
    public const string DB_PARAMS = 'dbParams';

    public function __construct(#[Named(MyApplication::DB_PARAMS)] private string $params) { }

    // ... other methods of the class
}
$binder->bindConstant(MyApplication::DB_PARAMS)->to('mysql:host=localhost;dbname=test');
```

