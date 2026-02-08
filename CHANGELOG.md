# Changelog

## 12.1.0 (2026-??-??)

### Heads up

* Added attributes from [stubbles/ioc-attributes](https://packagist.org/packages/stubbles/ioc-attributes) to replace all annotation based features. Annotations are now deprecated, and support for annotations will be removed with release 13.0.0. All attributes can be found in the `stubles\ioc\attributes` namespace. Attributes have the same name as the previous annotations, with one exception: `@List` became `#[Listing]` due to "list" being a reserved keyword in PHP which can't be used for class names.
  * `#[ImplementedBy(string $class, ?string $environment = null)]` (replaces `@ImplementedBy`), is repeatable and applicable on interfaces (technically it can be applied to classes, but is only recognized on interfaces by stubbles/ioc)
  * `#[Listing(string $name)]` (replaces `@List`), applicable on methods and parameters
  * `#[Map(string $name)]` (replaces `@Map`), applicable on methods and parameters
  * `#[Named(string $name)]` (replaces `@Named`), applicable on methods and parameters
  * `#[Property(string $name)]` (replaces `@Property`), applicable on methods and parameters
  * `#[ProvidedBy(string $providerClass)]` (replaces `@ProvidedBy`), applicable on classes
  * `#[Singleton]` (replaces `@Singleton`), applicable on classes

## 12.0.0 (2025-11-29)

### BC breaks

* raised minimum required PHP version to 8.3

### Other changes

* ensured compatibility with PHP 8.5

## 11.0.0 (2024-01-01)

### BC breaks

 * raised minimum required PHP version to 8.2
 * changed method declaration of `stubbles\ioc\InjectionProvider::get()` and `stubbles\ioc\binding\Binding::getInstance()` to include the return type hint `mixed`
 * changed type hint for param `$name` of `stubbles\ioc\binding\Binding::getInstance()` to `string|ReflectionClass|null`
 * changed return type hint of `stubbles\Environment::registerExceptionHandler()` and `stubbles\Environment::registerErrorHandler()` from none to `bool|object`
 * added mixed type hint to parameters and return values that were previously without any type hint

## 10.2.0 (2019-12-25)

* `stubbles\environments\errorhandler\LogErrorHandler` now logs request uri as additional field if present
* `stubbles\environments\exceptionhandler\AbstractExceptionHandler` now logs request uri as additional field if present
* `stubbles\ExceptionLogger::log()` now accepts the request id as optional second argument

## 10.1.0 (2019-12-18)

* added new optional parameter `$default = null` to `stubbles\ioc\binding\Session::value()`

## 10.0.0 (2019-12-13)

### BC breaks

* added return type `void` to various methods:
  * `stubbles\ioc\module\BindingModule::configure()`
  * `stubbles\ioc\binding\Session::putValue()`
  * `stubbles\environments\exceptionhandler\ExceptionHandler::handleException()`
* removed parameter `$context` from all methods of `stubbles\environments\errorhandler\ErrorHandler`, parameter is deprecated since PHP 7.2

### Other changes

* added more phpstan related type hints

## 9.0.0 (2019-11-12)

### BC breaks

* raised minimum required PHP version to 7.3
* param `$projectPath` of `stubbles\ioc\module\BindingModule::configure()` is not optional anymore

### Other changes

* fixed various type issues

## 8.0.1 (2016-07-30)

* fixed bug that typed map bindings lead to a `\TypeError`

## 8.0.0 (2016-07-22)

### BC breaks

* raised minimum required PHP version to 7.0.0
* introduced scalar type hints and strict type checking
* removed `stubbles\Rootpath`, use `stubbles\values\Rootpath` instead, was deprecated since 7.1.0
* removed `stubbles\ResourceLoader`, use `stubbles\values\ResourceLoader` instead, was deprecated since 7.1.0

### Other changes

* removed error handler for `E_RECOVERABLE_ERROR` of type argument type violations, was superseded by PHP's `\TypeError`

## 7.1.1 (2016-07-06)

* exception handler can now work with `\Error`

## 7.1.0 (2016-06-08)

* deprecated `stubbles\Rootpath`, use `stubbles\values\Rootpath` instead, will be removed with 8.0.0
* deprecated `stubbles\ResourceLoader`, use `stubbles\values\ResourceLoader` instead, will be removed with 8.0.0

## 7.0.0 (2016-01-15)

* split off from [stubbles/core](https://github.com/stubbles/stubbles-core)

### BC breaks

* raised minimum required PHP version to 5.6
* changed `stubbles\ioc\module\BindingModule::configure()` to accept an optional second parameter `$projectPath`
* moved `stubbles\ioc\App`, to `stubbles\App` instead
* removed `stubbles\lang\Mode`, use `stubbles\Environment` instead
* removed `stubbles\lang\DefaultMode::prod()`, use `stubbles\environments\Production` instead
* removed `stubbles\lang\DefaultMode::dev()`, use `stubbles\environments\Development` instead
* moved `stubbles\ioc\modules\Runtime` to `stubbles\Runtime`
* moved `stubbles\lang\errorhandler\ExceptionLogger` to `stubbles\ExceptionLogger`
* moved classes and functions from `stubbles\lang` to `stubbles`
  * moved `stubbles\lang\ResourceLoader` to `stubbles\ResourceLoader`
  * moved `stubbles\lang\Rootpath` to `stubbles\Rootpath`
* property `mode` of annotation `@ImplementedBy` must now be `environment`

### Other changes

* added `stubbles\ioc\Binder::createInjector(callable ...$applyBindings)`

## Older releases

* See [stubbles/core](https://github.com/stubbles/stubbles-core).
