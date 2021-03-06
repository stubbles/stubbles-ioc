ResourceLoader
--------------

_Deprecated since 7.1.0, use stubbles\values\ResourceLoader instead, will be removed with 8.0.0._

## Description

The `stubbles\ResourceLoader` allows to load resources from different locations.
It relies on the root path as described below.

In _stubbles/ioc_, a resource is defined as any kind of file which is located in
the path `src/main/resources` of the current project, or in `src/main/resources`
of any other Composer package located in `vendor`.

## Methods

### `open($resource, $withClass = 'stubbles\streams\file\FileInputStream')`

Opens the given resource to read its contents using the given `$withClass`. This
class must accept the resource path as constructor argument. By default the
class `stubbles\streams\file\FileInputStream` will be used, but the package
[stubbles/streams](https://github.com/stubbles/stubbles-streams) which provides
this class must be required in your project.

Resource can either be a complete path to a resource or a local path. In case it
is a local path it is searched within the `src/main/resources` folder of the
current project.

It is not possible to open resources outside of the root path by providing a
complete path, a complete path must always lead to a resource located within the
root path.

### `load($resource, callable $loader = null)`

Loads resource contents. Resource can either be a complete path to a resource or
a local path. In case it is a local path it is searched within the
`src/main/resources` folder of the current project.

It is not possible to load resources outside of the root path by providing a
complete path, a complete path must always lead to a resource located within the
root path.

In case no `$loader` is given the resource will be loaded with
`file_get_contents()`. The given `$loader` must accept a path and return the
result from the load operation:

```php
$props = $resourceLoader->load(
        'some/properties.ini',
        function($path) { return Properties::fromFile($path); }
);
```

### `availableResourceUris($resourceName)`

Returns a list of all available URIs for a resource. The returned list is sorted
alphabetically, meaning that local resources of the current project are always
returned as first entry if they exist, and all vendor resources after. Order of
vendor resources is also in alphabetical order of vendor/package names.

Rootpath
--------

_Deprecated since 7.1.0, use stubbles\values\Rootpath instead, will be removed with 8.0.0._

## Description

The root path within a project is represented by `stubbles\Rootpath`. It is
defined as the path in which the whole application resides. When an instance is
created and no argument is provided, the class will calculate the root path by
checking the following locations:

 * In case the application is inside a phar, it's the directory where the phar
   is stored.
 * Try to locate the `vendor/autoload.php` file generated by Composer, and go up
   one above `vendor/..`.

For unit tests it can be useful to supply the actual root path to be used for
the test directly when constructing the class.

## Methods

### `to(...$path)`

Returns absolute path to given local path. Supports arbitrary lists of arguments,
e.g. `$rootpath->to('src', 'main', 'php', 'Example.php')` will return
`/absolute/path/to/root/src/main/php/Example.php`.

### `contains($path)`

Checks if given path is located within root path.

### `sourcePathes()`

Returns a list of all source pathes defined for the autoloader. It relies on
autoloader files generated by Composer. If no such autoloader is present the
list of source pathes will be empty.
