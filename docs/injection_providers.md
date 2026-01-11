Injection providers
-------------------

In some rare cases there are dependencies that should not or could not be
created by the _stubbles/ioc_, as they are complex to create. One example could
be database connection objects, like `PDO` instances. In some cases you might
already have a factory which creates and configures objects for you and you do
not want to completely switch to _stubbles/ioc_ but integrate your factory so
it can provide the objects for the container.

To hook into the creation of the dependencies, you may write you own custom
provider by implementing the `stubbles\ioc\InjectionProvider` interface. This
interface requires you to implement one method only:

```php
interface InjectionProvider {
    public function get(?string $name = null): mixed;
}
```

The following example shows how to use this feature to inject a `PDO` instance:

```php
namespace example;
class MyApplication {
    public function __construct(private \PDO $pdo) { }
}
```

The class `MyApplication` requires an instance of [PDO](http://www.php.net/pdo).
If you take a look at the PDO documentation you will see that the constructor
requires parameters for the connection and the username and password. As `PDO`
is an internal class, you cannot add a `#[Named]` attribute and thus, _stubbles/ioc_
is not able to create this object.

The solution is to implement a provider, which creates the `PDO` instance:

```php
namespace example;
use PDO;
use stubbles\ioc\InjectionProvider;
/**
 * Provider to create PDO instances
 */
class PDOProvider implements InjectionProvider {

    public function get(?string $name = null): PDO
    {
        // get the connection parameters from any source
        $dsn  = MyRegistry::get('pdoDsn');
        $user = MyRegistry::get('pdoUser');
        $pass = MyRegistry::get('pdoPassword');
        // create the object
        return new PDO($dsn, $user, $pass);
    }
}
```

This provider can now be bound to the type `PDO` and will be used to create all
`PDO` instances:
```php
// create an instance of the provider and use it for the bindings
$provider = new PDOProvider();
$binder->bind(PDO::class)->toProvider($provider);
$injector = $binder->getInjector();
$app = $injector->getInstance(MyApplication::class);
```

When creating the `MyApplication` instance, the injector requires to create an
instance of the class `PDO`. This type has been bound to a provider (`PDOProvider`)
and thus, the injector delegates the creation of the PDO instance to this
provider object. In this object you can do whatever is needed to create the
actual object. In the example, a registry is used to fetch the connection
parameters for the PDO connection object.

## Providers as first-class-citizens for dependency injection

The above example can be improved by not binding the `PDO` class to the given
provider instance but to a provider class:

```php
$binder->bind(PDO::class)->toProviderClass(PDOProvider::class);
```

This has two advantages:

 * The `PDOProvider` class is initialized lazily just when it is required.
 * The `PDOProvider` class can now be subject to dependency injection itself.

To demonstrate the last point we extend the `PDOProvider` class a bit:

```php
namespace example;
use stubbles\ioc\InjectionProvider;
use stubbles\ioc\attributes\Named;
/**
 * Provider to create PDO instances
 */
class PDOProvider implements InjectionProvider {
    public function __construct(
        #[Named('pdoDsn')] private string $dsn,
        #[Named('pdoUser')] private string $user,
        #[Named('pdoPass')] private string $pass
    ) { }

    public function get(?string $name = null): PDO {
        return new PDO($this->dsn, $this->user, $this->pass);
    }
}
```

As you can see we attribute the constructor parameters of the provider and
inject the required constructor data for the `PDO` class into the provider
so the provider can use this later for constructing the `PDO` instance. Now
we just need to modify our bindings:

```php
$binder->bind(PDO::class)->toProviderClass(PDOProvider::class);
$binder->bindConstant('pdoDsn')->to('mysql:dbname=testdb;host=127.0.0.1');
$binder->bindConstant('pdoUser')->to('root');
$binder->bindConstant('pdoPass')->to('secretPassword');

$injector = $binder->getInjector();
$app = $injector->getInstance(MyApplication::class);
```

## Default providers

In case you have an interface and an implementation, but can not create the
implementation instance with the [default implementations](default_implementaions.md)
`#[ImplementedBy]` attribute, the `#[ProvidedBy]` attribute is another opportunity:

```php
use stubbles\ioc\attributes\ProvidedBy;
/**
 * All Persons should be instantiated using the PersonProvider class.
 */
#[ProvidedBy(example\PersonProvider::class)]
interface Person {
    public function sayHello(): string;
}
```

This has the same effect as

```php
$binder->bind(Person::class)->toProviderClass(PersonProvider::class);
```

but is available automatically with the attribute, without the need to create
this binding explicitly.
