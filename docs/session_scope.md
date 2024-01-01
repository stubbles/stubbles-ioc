The session scope
-----------------

Session scope means that an instance is only created once in a session, and not
a new one for each request.

_stubbles/ioc_ is prepared to support the session scope. It doesn't offer an own
session scope implementation, but it's very easy to implement one. For this, the
`stubbles\ioc\binding\BindingScope` interface has to be implemented, which only
consists of one method:

```php
public function getInstance(ReflectionClass $impl, InjectionProvider $provider): mixed
```

Within this method the implementation has to decide whether there is already an
instance of the class denoted by `$impl` within the session, or if a new
instance has to be created. Luckily the creation of the new instance is
delegated to `$provider`. An very simplistic approach to implement the session
scope might look like that:

```php
namespace example;
use ReflectionClass;
use stubbles\ioc\InjectionProvider;
use stubbles\ioc\binding\BindingScope;

class ExampleSessionScope implements BindingScope
{
    public function getInstance(ReflectionClass $impl, InjectionProvider $provider): mixed
    {
        if (!isset($_SESSION[$impl->getName()]) {
            $_SESSION[$impl->getName()] = $provider->get();
        }

        return $_SESSION[$impl->getName()];
    }
}
```

To make the session scope available add it to the binder instance:

```php
$binder->setSessionScope(new ExampleSessionScope());
```

Now you can bind classes to the session scope:

```php
$binder->bind('Person')->to('Mikey')->inSession();
```

Please note that the call to `inSession()` will throw a
`\RuntimeException` in case no session scope was added before. This means you
can only bind to the session scope after a session scope implementation was
added to the binder.
