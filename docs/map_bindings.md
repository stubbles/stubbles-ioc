Map bindings
------------

Map bindings are almost the same as [list bindings](list_bindings.md), but while
list bindings are kind of anonymous (you can see them as indexed arrays) map
bindings allow for named values (which compares to associative arrays). The
functionality of map bindings is identical to list bindings, this includes typed
and untyped map bindings. The only difference is the `#[Map]` attribute and how
map bindings are created with the binder:

```php
use stubbles\ioc\attributes\Map;
class PluginManager
{
    private $plugins;

    #[Map(Plugin::class)]
    public __construct($plugins)
    {
        $this->plugins = $plugins;
    }

    // Methods for managing plugins
}
```
Bindings can be defined as follows:

```php
$binder->bindMap(Plugin::class)
    ->withEntry('security', new SecurityPlugin()) 
    // alternatively: ->withEntry('security', SecurityPlugin::class)
    ->withEntryFromProvider('coolStuff', CoolPluginProvider::class); // provides AnotherCoolPlugin
```

## Closure bindings

Since release 2.1.0 it is also possible to bind values using closures:

```php
$binder->bindMap(Plugin::class)
    ->withEntryFromClosure('extended', fn() => EvenMoreSecurityPlugin());
```

This comes in handy when a value should be initialized lazy because it's too
much effort to create it at the very moment, but using a separate injection
provider would be overblown. The code inside the closure has to create the value,
it doesn't have any access to the injector, so if there any dependencies they
must be available at the moment the closure is created.
