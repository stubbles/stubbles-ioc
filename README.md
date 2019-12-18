stubbles/ioc
============

Dependency injection.


Build status
------------

[![Build Status](https://secure.travis-ci.org/stubbles/stubbles-ioc.png)](http://travis-ci.org/stubbles/stubbles-ioc) [![Coverage Status](https://coveralls.io/repos/github/stubbles/stubbles-ioc/badge.svg?branch=master)](https://coveralls.io/github/stubbles/stubbles-ioc?branch=master)

[![Latest Stable Version](https://poser.pugx.org/stubbles/ioc/version.png)](https://packagist.org/packages/stubbles/ioc) [![Latest Unstable Version](https://poser.pugx.org/stubbles/ioc/v/unstable.png)](//packagist.org/packages/stubbles/ioc)


Installation
------------

_stubbles/ioc_ is distributed as [Composer](https://getcomposer.org/)
package. To install it as a dependency of your package use the following
command:

    composer require "stubbles/ioc": "^10.1"


Requirements
------------

_stubbles/ioc_ requires at least PHP 7.3.


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

    public function __construct(Engine $engine, Tire $tire, Person $driver) {
        $this->engine = $engine;
        $this->tire   = $tire;
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

    $bmw    = new BMW($engine, $tire, $schst);
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
  ["driver:private"]=>
  NULL
  ["engine:private"]=>
  object(TwoLitresEngine)#37 (0) {
  }
  ["tire:private"]=>
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


Further features
----------------

* [Optional injection](docs/optional_injection.md)
* [Implicit bindings](docs/implicit_bindings.md)
* [Default implementations](docs/default_implementations.md)
* [Inject instances](docs/inject_instances.md)
* [Singletons](docs/singleton_scope.md)
* [Session scope](docs/session_scope.md)
* [Named parameters](docs/named_parameters.md)
* [Constant values](docs/constant_values.md)
* [List bindings](docs/list_bindings.md)
* [Map bindings](docs/map_bindings.md)
* [Closure bindings](docs/closure_bindings.md)
* [Injection providers](docs/injection_providers.md)
* [Create the whole application](docs/application.md)
* [Application properties](docs/application_properties.md)
* [Runtime environment](docs/runtime_environment.md)
* [Rootpath & Resource loader](docs/rootpath_resourceloader.md)
