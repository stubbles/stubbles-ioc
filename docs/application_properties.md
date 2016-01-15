Application properties
----------------------

When you build an application it can be useful to allow certain things to be
configurable. That could be pathes where files are stored, or specific settings
that influence how the application behaves or what it does. That's where
property bindings comes into play.


## Properties

Properties are useful to configure application settings. That could be database
connection settings, enabling or disabling certain features, or other things
that are useful to do via simple configuration files. The application properties
support that by reading a file _path/to/your/project/config/config.ini_
and to add a binding for properties which allows other classes in the
application to get those properties injected.

There are two ways to get the properties from this file injected: either by
receiving an instance of `stubbles\values\Properties` or by receiving single
property values.

### Instance of `stubbles\values\Properties`

To get access to all property values, the class should declare a dependency to
`stubbles\values\Properties` named _config_:

```php
namespace example;
use stubbles\values\Properties;
class Example
{
    /**
     * @type  Properties
     */
    private $properties;

    /**
     * constructor
     *
     * @param  Properties  $properties
     * @Named('config')
     */
    public function __construct(Properties $properties)
    {
        $this->properties = $properties;
    }

    // ... useful methods to do something ...
}
```

Now the class has access to all properties via the `stubbles\values\Properties`
instance. See [documentation of stubbles/values](https://github.com/stubbles/stubbles-values#stubblesvaluesproperties)
for details on how to access single values.

### Receiving single property values

If you require a single property value only it might be too much to receive a
whole `stubbles\values\Properties` instance. Instead you want the single value
only. This could be done by using constants:

```php
namespace example;
class AnotherExample
{
    /**
     * @type  string
     */
    private $roland;

    /**
     * constructor
     *
     * @param  string  $roland
     * @Named('example.roland')
     */
    public function __construct($roland)
    {
        $this->roland = $roland;
    }

    ... useful methods to do something ...
}
```

Here, the configuration value _example.some.setting_ will be injected. However,
it must be in the _config_ section of the _path/to/your/project/config/config.ini_
file:

```
[config]
example.roland = "TB 303"
```

Please note that only properties in the _config_ section of the file are
available for injection. For configurations in any other section you need to use
the instance as described above.


## Environment depending property values

In some situations it is necessary that a property value is different depending
on the current runtime environment, e.g. for a service endpoint which is
different in development and production. The property file can contain a section
for each environment:

```
[config]
example.roland = "TB 303"

[PROD]
foo.service.endpoint="http://foo.example.com/"

[DEV]
foo.service.endpoint="http://foo.example.qa/"
```

Here, the value of the injected property _foo.service.endpoint_ is different in
production and development environment. The lookup pattern for properties is as
follows:

1. If property is defined for current environment use that.
1. User property defined in _config_ section.

In case the property is not defined for the current environment nor in the
_config_ section and the injection is not optional this will cause a
`stubbles\ioc\binding\BindingException`.


## Pathes as properties

Often, it is also useful to get pathes injected so that they are not fixed
within classes, which makes them hard to test or even impossible to use on
another system when those pathes are not only fixed but also global and not
relative.

By default, application properties will provide constants for three different
pathes:

 * config, _stubbles.config.path_, pointing to _$projectPath/config_
 * log, _stubbles.log.path_, pointing to _$projectPath/log_

You can add more pathes using the `addPathType()` method:

```php
namespace example;
use net\stubbles\App;
class MyApplication extends App
{
    /**
     * returns a list of binding modules used to wire the object graph
     *
     * @return  array
     */
    public static function __bindings()
    {
        return [
                self::runtime()->addPathType('docroot'),
        ];
    }
}
```

Now, a path pointing to _$projectPath/docroot_ becomes available for injection
using the _stubbles.docroot.path_ constant.

## Current working directory

_Available since release 2.1.0._

While the current working directory can be retrieved simply by calling the PHP
function `getcwd()` this might not always be desirable, as this makes test cases
harder to create so that they run on more than just the developer's machine. The
properties binding module provides a way of making the current working directory
available for injection:

```php
namespace example;
use net\stubbles\App;
class MyApplication extends App
{
    public static function __bindings()
    {
        return [
                self::runtime()->withCurrentWorkingDirectory()
        ];
    }
}
```

Now, the current working directory is available as constant binding under the
name `stubbles.cwd`.


## Hostname

_Available since release 2.1.0._

Similar to the current working directory it might not always be desirable to
call `php_uname('n')` in order to retrieve the hostname of the current machine.
Also, this only provides a non qualified hostname, whereas sometimes a fully
qualified hostname is required. Retrieving this is much harder, as there is no
common way to do this regardless of the platform the application is currently
running on. The properties binding module provides a way of making the hostname
available for injection:

```php
namespace example;
use net\stubbles\App;
class MyApplication extends App
{
    public static function __bindings()
    {
        return [
                self::runtime()->withHostname()
        ];
    }
}
```

This makes both the non and the fully qualified hostname available as constant
bindings. The name for the non qualified hostname is `stubbles.hostname.nq`,
whereas the name for the fully qualified hostname is `stubbles.hostname.fq`.

Please note: the method will try different ways to resolve the hostname. While
non qualified hostname can be guaranteed to be available, the fully qualified
hostname might be empty in case it could not be retrieved. Your application
should be able to react on such a situation.
