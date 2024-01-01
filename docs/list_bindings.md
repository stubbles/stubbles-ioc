List bindings
-------------

List bindings are a way to inject a list of values (aka an PHP array) into a
class. The great thing about list bindings is that the elements of the list can
be created using dependency injection as well, and that adding values to the
list can be spread over several different binding modules. Suppose we have an
interface like this:

```php
interface Plugin
{
    function getName()
    function getAuthor()
    function getVersion()
    function getDependencies()
}
```

This interface has to be implemented by every plugin. Let's assume we have the
following two plugin definitions:

```php
class SecurityPlugin implements Plugin
{
   // Implementation
}
```

```php
class AnotherCoolPlugin implements Plugin
{
   // Implementation
}
```

We have two plugins that should be managed by our application which uses the
PluginManager for this:

```php
class PluginManager
{
    /**
     * @List(example\Plugin.class)
     */
    public __construct(private array $plugins) { }

    // Methods for managing plugins
}
```

The PluginManager only knows about the Plugin interface, and never about
concrete Plugin implementations. We can now use list bindings to tell the
injector how to construct the list:

```php
$binder->bindList(Plugin::class)
    ->withValue(new SecurityPlugin()) // alternatively: ->withValue(SecurityPlugin::class)
    ->withValueFromProvider('example\\CoolPluginProvider'); // provides AnotherCoolPlugin
```

_stubbles/ioc_ will now take care of creating the plugin list and injecting it
into the PluginManager. Additionally you don't have to create the concrete
values in this place, but defer their creation to _stubbles/ioc_ as well. In
this example both the CoolPluginProvider and AnotherCoolPlugin are subject to
dependency injection and also created by the Stubbles IoC container.

Alternatively if a value provider is too much you can simply provide the class
name - _stubbles/ioc_ will create the instance then and do any necessary
injections for this instance.

It is also possible to separate the adding of values to the list over different
binding modules:

```php
class BindingModuleOne implements BindingModule
{
    /**
     * configure the binder
     */
    public function configure(Binder $binder): void
    {
         $binder->bindList(Plugin::class)
            ->withValue(new SecurityPlugin()); 
            // alternatively: ->withValue(SecurityPlugin::class)
    }
}

class BindingModuleTwo implements BindingModule
{
    /**
     * configure the binder
     */
    public function configure(Binder $binder): void
    {
         $binder->bindList(Plugin::class)
            ->withValueFromProvider(CoolPluginProvider::class);
    }
}
```

If both binding modules are used to configure the same binder the result will be
a list which contains both the SecurityPlugin and the AnotherCoolPlugin. The
reason is that both list bindings use the same name. If they would have used
different names it would have resulted in different lists.

## Typed lists

All examples from above refer on how to create typed lists. A typed list means
that each entry within the list is of the same type. This is also enforced by
_stubbles/ioc_. If a value gets added which is not of the required type a
`stubbles\ioc\binding\BindingException` will be thrown when the list is created.

The type of the list is defined with the value of the `@List` annotation. In the
PluginManager above you can see such a definition. The type is also used to
identify the list when you add values to it, even though only the type name is
used here without the _.class_ addition.

## Untyped lists

List bindings can also be untyped. To create an untyped list, simply use a
string name as value for the `@List` annotation:

```php
class Configuration
{
    private $config;

    /**
     * @List('config')
     */
    public __construct($config)
    {
        $this->config = $config;
    }

    // more methods
}

$binder->bindList('config')
    ->withValue('foo')
    ->withValueFromProvider('example\\ConfigValueProvider'); // provides another config value
```

## Named bindings and list bindings

It is not possible to annotate list bindings with the `@Named` annotation. As
lists already are identified with their own names it is not required to use
`@Named` for lists.

## Closure bindings

Since release 2.1.0 it is also possible to bind values using closures:

```php
$binder->bindList('config')
    ->withValueFromClosure(fn() => 'foo';);
```

This comes in handy when a value should be initialized lazy because it's too
much effort to create it at the very moment, but using a separate injection
provider would be overblown. The code inside the closure has to create the
value, it doesn't have any access to the injector, so if there any dependencies
they must be available at the moment the closure is created.
