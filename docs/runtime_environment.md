Runtime environment
-------------------

Applications often have different runtime environments where they are running.
For instance, there may be the production environment, and this may be different
from the environment the application runs in while it is developed. _stubbles/ioc_
supports different runtime environments.


## Inclusion via injection

In your applications you should typehint against `stubbles\Environment` and not
refer to any of the concrete environment instances. This will ensure that you
refer to the mode selected by your application. To provide the runtime
environment via injection you may want to use the `stubbles\Runtime`. You
can give it the requested runtime environment as constructor argument - if it is
not supplied the binding module will fall back to
`stubbles\environments\Production`.


## Create your own environment

By implementing the `stubbles\Environment` interface you can create your own
runtime environement implementation.


## More useful methods on environment instances

Every environment instance offers the following methods:

   * `name()` returns the name of the mode (_PROD_, _TEST_, _STAGE_ or _DEV_)
   * `registerExceptionHandler($projectPath)`
   * `registerErrorHandler($projectPath)`
   * `isCacheEnabled()` returns `true` if the cache is enabled for this mode and `false` if not.


## Exception handler

_stubbles/ioc_ offers two different exception handlers:

 * `stubbles\environments\exceptionhandler\ProdModeExceptionHandler` will issue
    a HTTP 500 response with the contents of the file _path/to/project/docroot/500.html_
    (if the file is not present it will use a default error message).
 * `stubbles\environments\exceptionhandler\DisplayExceptionHandler` displays the
    exception message and the stack trace of the exception.

Both handlers have logging enabled by default. To switch off logging use
`$exceptionHandler->disableLogging()`. Logged exceptions can be found at
_path/to/project/log/errors/YYYY/MM/exceptions-YYYY-MM-DD.log_.

See [PHP manual on exception handlers](http://php.net/set_exception_handler) for
more information.

### Your own exception handler

You can create your own exception handler by implementing the
`stubbles\environments\exceptionhandler\ExceptionHandler` interface.

## Error handler

For production the `stubbles\environments\errorhandler\DefaultErrorHandler` is
used. It combines some other error handlers which are executed in the following
order:

   * `stubbles\environments\errorhandler\IllegalArgumentErrorHandler` checks for
      errors of type `E_RECOVERABLE_ERROR` and if they denote a hurted type hint.
      If this is the case a `stubbles\lang\exceptions\IllegalArgumentException`
      is thrown.
   * `stubbles\environments\errorhandler\LogErrorHandler` will log any PHP
     errors that have not been handled by another error handler. Log files go
     to _path/to/project/log/errors/YYYY/MM/php-error-YYYY-MM-DD.log_.

See [PHP manual on exception handlers](http://php.net/set_error_handler) for
more information.

### Your own error handler

You can create your own error handler by implementing the
`stubbles\environments\errorhandler\ErrorHandler` interface.
