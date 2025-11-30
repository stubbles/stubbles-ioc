Applications
------------

_stubbles/ioc_ provides a way to construct the whole object graph for the
application in a simple manner. The `stubbles\App` class provides support
for this. You can use it by creating an own application class which extends
`stubbles\App` which drastically reduces bootstrap code required to run an
application. The bootstrap code then looks like this:

```php
example\MyApplication::create($pathToProject)
        ->run();
```

The `example\MyApplication` class has to look like this:

```php
namespace example;
use stubbles\App;
class MyApplication extends App
{
    /**
     * returns a list of binding modules used to wire the object graph
     *
     * @return  array
     */
    public static function __bindings(): array
    {
        return [
            new example\StuffRequiredForApplicationBindingModule(),
            'example\other\MoreStuffBindingModule'
        ];
    }

    public function __construct(private Controller $controller) { }

    public function run()
    {
        // application running logic here, for instance calling the controller
    }
}
```

Important point is the `__bindings()` method only: it has to return a list of
binding modules which define all bindings required to run this application.
Constructor and run method are only example-wise to show that an instance of
this class is created, dependencies are injected and that any further
application logic is then triggered via the `run()` method. Your own application
class may look different, important is only that it needs to have the
`__bindings()` method in order to use `YourAppClass::create()`. The `create()`
method is provided by `stubbles\App` which uses [late static binding](http://php.net/get_called_class)
to detect the correct application class to instantiate.

The list returned from `__bindings()` can contain both full qualified class
names of binding module implementations as well as binding module instances.

## Binding modules

Binding modules are meant to group bindings which logically belong together.
This helps to group your bindings so that you don't need to have them all in the
same place. A binding module is a very simple class implementing the
`stubbles\ioc\module\BindingModule` interface:

```php
namespace example;
use stubbles\ioc\Binder;
use stubbles\ioc\module\BindingModule;
class MyApplicationBindingModule implements BindingModule
{
    /**
     * configure the binder
     *
     * @param  Binder  $binder
     */
    public function configure(Binder $binder): void
    {
        $binder->bind('example\\SomeInterface')
               ->to('example\\SomeImplementation');

        ... more bindings which might make sense here ...
    }
}
```

Please note that binding modules itself are **not** subject to dependency
injection. It should not be required in this place. If you need configurable
bindings, the binding module implementation may provide methods to set the
configuration, and should be called in your `__bindings()` method:

```php
namespace example;
use stubbles\ioc\App;
class MyApplication extends App
{
    /**
     * returns a list of binding modules used to wire the object graph
     *
     * @return  array<BindingModule|Closure>
     */
    public static function __bindings(): array
    {
        return [
            ExampleBindingModule::newInstance()
                ->withCoolStuff()
                ->addAwesomeness(),
            MoreStuffBindingModule::class
        ];
    }

    ...  other methods here ...
}
```

If done as in the example the configuration methods need to return the binding
module instance of course.


### Provided binding modules

_stubbles/ioc_ already provides `stubbles\Runtime` which binds an instance of
`stubbles\Environment` (see [environments](environments.md)). It also ensures
that error and exception handlers for this environment are registered. The
required environment can be given to the constructor, if none is given it falls
back to environment mode from `stubbles\environments\Production`.

If you need different runtime environments it is advised to extend the runtime
binding module and overwrite its `protected function getFallbackMode()` method
which should provide the mode depending on your requirements and enviroments.

In case the application doesn't add `stubbles\Runtime` to the list of binding
modules it will automatically be added by `stubbles\App`.


## Dynamic bindings
_Available since release 2.1.0_

Sometimes it is helpful to define some bindings on the fly in the `__bindings()`
method without having to resort to create a separate binding module
implementation. Instead of returning a binding module instance or class name, a
closure can be returned as well:

```php
namespace example;
use stubbles\App;
use stubbles\ioc\Binder;
class MyApplication extends App
{
    /**
     * returns a list of binding modules used to wire the object graph
     *
     * @return  array<BindingModule|Closure>
     */
    public static function __bindings(): array
    {
        return [
            new StuffRequiredForApplicationBindingModule(),
            MoreStuffBindingModule::class,
            function(Binder $binder): void
            {
                $binder->bindConstant('answer')->to(42);
            }
        ];
    }
}
```

The closure retrieves an instance of `stubbles\ioc\Binder` as parameter and can
add any kind of binding it likes. This can help in situations where you require
one or more bindings for a single app only. There is no limitation on how many
closures the array to return can contain.
