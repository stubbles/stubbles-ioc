Default implementations
-----------------------

Very often you only use one concrete implementation of an interface in your
application and only added the interface or abstract class to make dependent
classes better testable. To avoid having to bind all of your interfaces to the
concrete implementations you may specify a default implementation which will be
used. To achieve this, add the `@ImplementedBy` annotation to your interface.

```php
/**
 * All Persons should be bound to the class Schst unless Person is bound
 *
 * @ImplementedBy(Schst.class)
 */
interface Person {
    public function sayHello(): string;
}


$person = $injector->getInstance('Person'); // $person is now an instance of Schst
```

It should be noted though, that once a specific binding for `Person` is added to
the binder that the annotation is not considered anymore:

```php
    $binder->bind('Person')->to('Mikey');
    $person = $binder->getInjector()->getInstance('Person');
```

In this example, `$person` is now an instance of `Mikey` and not of `Schst` -
the explicit binding overruled the annotated binding.


### Default implementations per runtime environment

_Available since release 6.0.0_

Sometimes it is required to have a different default implementation per runtime
environment, e.g. a mock implementation for DEV and the real service
implementation for PROD. This can be accomplished by specifying the environment:

```php
/**
 * @ImplementedBy(environment="DEV", class=Mikey.class)
 * @ImplementedBy(Schst.class)
 */
interface Person {
    public function sayHello(): string;
}
```

Now, depending on the runtime mode, the according implementation will be chosen.
If the runtime environment is DEV, an instance of `Mikey` will be created, and
`Schst` for all other runtime environments.

Please note that you should always specify one default without a runtime
environment, otherwise a `stubbles\ioc\binding\BindingException` will be thrown
in case no implementation can be found for the current runtime environment.
