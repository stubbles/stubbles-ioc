stubbles/ioc
============

Dependency injection.


Build status
------------

[![Build Status](https://secure.travis-ci.org/stubbles/stubbles-ioc.png)](http://travis-ci.org/stubbles/stubbles-ioc) [![Coverage Status](https://coveralls.io/repos/stubbles/stubbles-ioc/badge.png?branch=master)](https://coveralls.io/r/stubbles/stubbles-ioc?branch=master)

[![Latest Stable Version](https://poser.pugx.org/stubbles/ioc/version.png)](https://packagist.org/packages/stubbles/ioc) [![Latest Unstable Version](https://poser.pugx.org/stubbles/ioc/v/unstable.png)](//packagist.org/packages/stubbles/ioc)


Installation
------------

_stubbles/ioc_ is distributed as [Composer](https://getcomposer.org/)
package. To install it as a dependency of your package use the following
command:

    composer require "stubbles/ioc": "^7.0"


Requirements
------------

_stubbles/ioc_ requires at least PHP 5.6.


Inversion of Control
--------------------

_stubbles/ioc_ provides a very simple-to-use but still powerful [inversion of
control container](http://martinfowler.com/articles/injection.html), which
supports constructor and setter based dependency injection. The IoC container of
_stubbles/ioc_ is modeled after [Google Guice](http://code.google.com/p/google-guice/)
and makes use of type hinting annotations. If you've never heard of type hinting
or annotations, you should at first read the sections on these two topics:

 * [Section on 'type hinting' in the PHP manual](http://www.php.net/language.oop5.typehinting)
 * [Annotations](https://github.com/stubbles/stubbles-reflect#annotations) Section on annotations in the _stubbles/reflect_.


### The example code

Imagine, you are building a car configurator. To follow the rules of good
design, you define interfaces for all components of a car and provide several
classes that implement these components.

The interfaces in you application include:

```php
interface Car {
    public function moveForward($miles);
}
interface Person {
    public function sayHello();
}
interface Tire {
    public function rotate();
}
interface Engine {
    public function start();
}
```

The implementations are:

```php
class BMW implements Car {
    private $driver;
    private $engine;
    private $tire;

    public function __construct(Engine $engine, Tire $tire) {
        $this->engine = $engine;
        $this->tire = $tire;
    }
    public function setDriver(Person $driver) {
        $this->driver = $driver;
    }
    public function moveForward($miles) {
        $this->driver->sayHello();
        $this->engine->start();
        $this->tire->rotate();
    }
}

class Schst implements Person {
    public function sayHello() {
        echo "My name is Stephan\n";
    }
}

class Goodyear implements Tire {
    public function rotate() {
        echo "Rotating Goodyear tire\n";
    }
}

class TwoLitresEngine implements Engine {
    public function start() {
        echo "Starting 2l engine\n";
    }
}
```


### Without the dependency injection framework

To create a new instance of an implementation of `Car` the following code is
required:

```php
    $tire   = new Goodyear();
    $engine = new TwoLitresEngine();
    $schst  = new Schst();

    $bmw    = new BMW($engine, $tire);
    $bmw->setDriver($schst);

    $bmw->moveForward(50);
```

Creating objects manually like this has several drawbacks:

 * Your application is bound to the concrete implementations instead of the
   interfaces
 * Changing the implementation means changing existing code, which might break it
 * The creation of objects is scattered throughout your application

Of course, real applications have a lot more classes, so things only get worse then.


### Enter 'Inversion of Control'

_stubbles/ioc_ tries to solve these problems by providing functionality to
handle all dependency injections for you. This keeps your application clean of
boilerplate code.

Furthermore, it allows you to centralize and/or modularize the definition of the
concrete implementations for your interfaces or abstract types.


### A simple example

To define the concrete implementations is done using an instance of `stubbles\ioc\Binder`:

```php
$binder = new \stubbles\ioc\Binder();
$binder->bind('Car')->to('BMW');
$binder->bind('Tire')->to('Goodyear');
$binder->bind('Person')->to('Schst');
$binder->bind('Engine')->to('TwoLitresEngine');
```

In this short code snippet, you bound the interfaces from the example above to
their concrete implementations.

If you now need an instance of the engine, you use the binder to create a
`stubbles\ioc\Injector`, which can be used to create the desired `Engine`:

```php
$injector = $binder->getInjector();
$engine = $injector->getInstance('Engine');
var_dump($engine);
```

This code snippet will now display:
```
object(TwoLitresEngine)#48 (0) {
}
```

As desired, it created an instance of the concrete implementation, that you
bound to the interface.

Next, you probably want to get an instance of `Car` using the same approach:

```php
    $injector = $binder->getInjector();
    $car = $injector->getInstance('Car');
    var_dump($car);
```

```
object(BMW)#33 (3) {
  ["driver:protected"]=>
  NULL
  ["engine:protected"]=>
  object(TwoLitresEngine)#37 (0) {
  }
  ["tire:protected"]=>
  object(Goodyear)#40 (0) {
  }
}
```

_stubbles/ioc_ created a new instance of `BMW`, as you bound it to `Car`, and as
the constructor of `BMW` requires a `Tire` and an `Engine` instance, it created
these instances as well. To determine the concrete classes to use, _stubbles/ioc_
used the bindings you defined in the `stubbles\ioc\Binder` instance.

What you also can see is, that Stubbles did not inject an object into the
`$driver` property, although you specified a binding for `Person`. _stubbles/ioc_
will *never* inject any dependencies via setter methods.


### Optional injection

Probably you do not want to inject an object every time, because the class will
work fine without the dependency. If a parameter has a default value and is
optional injection will be done using the default value.

```php
class BMWWithCoDriver extends BMW {
    private $codriver;

    public function __construct(Engine $engine, Tire $tire, CoDriver $codriver = null) {
        parent::__construct($engine, $tire);
        $this->codriver = $codriver;
    }

    public function moveForward($miles) {
        if (null !== $this->codriver) {
            $this->codriver->sayHello();
        }

        parent::moveForward($miles);
    }
}
```


### Implicit bindings

_stubbles/ioc_ does not force you to use interfaces in your type hints. If you are
already using concrete classes, there is no need to bind them, as _stubbles/ioc_
will implicitly bind the concrete class to itself:

```php
class Window {}

class BMW implements Car {
    private $driver;
    private $engine;
    private $tire;
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


### Default implementations

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
    public function sayHello();
}


$person = $injector->getInstance('Person'); // $person is now an instance of Schst
```

It should be noted though, that once a specific binding for `Person` is added to
the binder that the annotation is not considered anymore:

```php
    $binder->bind('Person')->to('Mikey);
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
    public function sayHello();
}
```

Now, depending on the runtime mode, the according implementation will be chosen.
If the runtime environment is DEV, an instance of `Mikey` will be created, and
`Schst` for all other runtime environments.

Please note that you should always specify one default without a runtime
environment, otherwise a `stubbles\ioc\binding\BindingException` will be thrown
in case no implementation can be found for the current runtime environment.


### The singleton scope

Multiple calls to `$injector->getInstance('Car');` will return different
objects. In most cases, this is probably what you want, as the IoC framework
behaves like the `new` operator. If you want to create only one instance of the
`BMW` class, you can easily convert the `BMW` class to a [singleton](http://en.wikipedia.org/wiki/Singleton_pattern).

```php
$binder = new stubbles\ioc\Binder();
$binder->bind('Car')->to('BMW')->asSingleton();
// other bindings

$injector = $binder->getInjector();
$bmw1 = $injector->getInstance('Car');
$bmw2 = $injector->getInstance('Car');

if ($bmw1 === $bmw2) {
    echo "Same object.\n";
}
```

Using `asSingleton()` makes sure that the instance is created only once and
subsequent calls to `getInstance()` will return the same instance.

Another way to treat a class as a singleton is using the `@Singleton`
annotation, which is used to annotate the class. The following example makes
sure, that the application uses only one instance of the class `Schst`:

```php
/**
 * @Singleton
 */
class Schst implements Person {
    public function sayHello() {
        echo "My name is Stephan\n";
    }
}
```

The following code will now create two instances of the class `BMW`, but both
should have a reference to the same `Schst` instance:

```php
$binder = new stubbles\ioc\Binder();
$binder->bind('Car')->to('BMW');
// other bindings

$injector = $binder->getInjector();
$bmw1 = $injector->getInstance('Car');
$bmw2 = $injector->getInstance('Car');

var_dump($bmw1);
var_dump($bmw1);
```

If you run the code snippet, you get the following output:
```
object(BMW)#34 (3) {
  ["driver:protected"]=>
  object(Schst)#50 (0) {
  }
  ["engine:protected"]=>
  object(TwoLitresEngine)#38 (0) {
  }
  ["tire:protected"]=>
  object(Goodyear)#41 (0) {
  }
}
object(BMW)#30 (3) {
  ["driver:protected"]=>
  object(Schst)#50 (0) {
  }
  ["engine:protected"]=>
  object(TwoLitresEngine)#44 (0) {
  }
  ["tire:protected"]=>
  object(Goodyear)#39 (0) {
  }
}
```

As you can see, the two `BMW` instances have different object handles (_#30_
and _#34_), but the `$driver` properties point to the same `Schst` instance
(object handle _#50_).

Implementing the singleton pattern never has been this easy.


### The session scope

Session scope means that an instance is only created once in a session, and not
a new one for each request.

_stubbles/ioc_ is prepared to support the session scope. It doesn't offer an own
session scope implementation, but it's very easy to implement one. For this, the
`stubbles\ioc\binding\BindingScope` interface has to be implemented, which only
consists of one method:

```php
/**
 * returns the requested instance from the scope
 *
 * @param  \ReflectionClass $impl concrete implementation
 * @param  \stubbles\ioc\InjectionProvider $provider
 * @return Object
 */
public function getInstance(\ReflectionClass $impl, InjectionProvider $provider)
```

Within this method the implementation has to decide whether there is already an
instance of the class denoted by `$impl` within the session, or if a new
instance has to be created. Luckily the creation of the new instance is
delegated to `$provider`. An very simplistic approach to implement the session
scope might look like that:

```php
namespace example;
use stubbles\ioc\InjectionProvider;
use stubbles\ioc\binding\BindingScope;

class ExampleSessionScope implements BindingScope
{
    /**
     * returns the requested instance from the scope
     *
     * @param  \ReflectionClass $impl concrete implementation
     * @param  \stubbles\ioc\InjectionProvider $provider
     * @return object
     */
    public function getInstance(\ReflectionClass $impl, InjectionProvider $provider)
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
`stubbles\lang\exception\RuntimeException` in case no session scope was added
before. This means you can only bind to the session scope after a session scope
implementation was added to the binder.


