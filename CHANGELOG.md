7.0.0 (2016-01-15)
------------------

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


Older releases
--------------

  * See [stubbles/core](https://github.com/stubbles/stubbles-core).