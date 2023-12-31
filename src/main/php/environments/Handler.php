<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles
 */
namespace stubbles\environments;
/**
 * Provides methods for error and exception handling in environments.
 *
 * The main reason for the trait is that stubbles\Environment must be an
 * interface to maintain backwards compatibility.
 */
abstract class Handler
{
    /** @var  class-string|object */
    private string|object|null $exceptionHandler = null;
    private ?string $exceptionHandlerMethod = null;
    /** @var  class-string|object */
    private string|object|null $errorHandler = null;
    private ?string $errorHandlerMethod = null;

    /**
     * sets the exception handler to given class and method name
     *
     * To register the new exception handler call registerExceptionHandler().
     */
    protected function setExceptionHandler(
        string|object $class,
        string $methodName = 'handleException'
    ): self {
        $this->exceptionHandler = $class;
        $this->exceptionHandlerMethod = $methodName;
        return $this;
    }

    /**
     * registers exception handler for current mode
     *
     * Return value depends on registration: if no exception handler set return
     * value will be false, if registered handler was an instance the handler
     * instance will be returned, and true in any other case.
     */
    public function registerExceptionHandler(string $projectPath): bool|object
    {
        if (null === $this->exceptionHandler) {
            return false;
        }

        $callback = $this->createCallback(
            $this->exceptionHandler,
            $this->exceptionHandlerMethod,
            $projectPath
        );
        set_exception_handler($callback);
        return $callback[0];
    }

    /**
     * sets the error handler to given class and method name
     *
     * To register the new error handler call registerErrorHandler().
     */
    protected function setErrorHandler(
        string|object $class,
        string $methodName = 'handle'
    ): self {
        $this->errorHandler = $class;
        $this->errorHandlerMethod = $methodName;
        return $this;
    }

    /**
     * registers error handler for current mode
     *
     * Return value depends on registration: if no error handler set return value
     * will be false, if registered handler was an instance the handler instance
     * will be returned, and true in any other case.
     */
    public function registerErrorHandler(string $projectPath): bool|object
    {
        if (null === $this->errorHandler) {
            return false;
        }

        $callback = $this->createCallback(
            $this->errorHandler,
            $this->errorHandlerMethod,
            $projectPath
        );
        set_error_handler($callback);
        return $callback[0];
    }

    /**
     * helper method to create the callback from the handler data
     */
    private function createCallback(
        string|object $class,
        string $methodName,
        string $projectPath
    ): callable {
        $instance = ((is_string($class)) ? (new $class($projectPath)) : ($class));
        return [$instance, $methodName];
    }
}
