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
namespace stubbles\environments\errorhandler;
/**
 * Container for a collection of PHP error handlers.
 */
class ErrorHandlers implements ErrorHandler
{
    /**
     * list of registered error handlers
     *
     * @type  \stubbles\environments\ErrorHandler[]
     */
    private $errorHandlers = [];

    /**
     * adds an error handler to the collection
     *
     * @param  \stubbles\environments\errorhandler\ErrorHandler  $errorHandler
     */
    public function addErrorHandler(ErrorHandler $errorHandler)
    {
        $this->errorHandlers[] = $errorHandler;
    }

    /**
     * checks whether this error handler is responsible for the given error
     *
     * This method is called in case the level is 0. It decides whether the
     * error has to be handled or if it can be omitted.
     *
     * @param   int     $level    level of the raised error
     * @param   string  $message  error message
     * @param   string  $file     filename that the error was raised in
     * @param   int     $line     line number the error was raised at
     * @param   array   $context  array of every variable that existed in the scope the error was triggered in
     * @return  bool    true if error handler is responsible, else false
     */
    public function isResponsible(
            int $level,
            string $message,
            string $file = null,
            int $line = null,
            array $context = []
    ): bool
    {
        foreach ($this->errorHandlers as $errorHandler) {
            if ($errorHandler->isResponsible($level, $message, $file, $line, $context) == true) {
                return true;
            }
        }

        return false;
    }

    /**
     * checks whether this error is supressable
     *
     * @param   int     $level    level of the raised error
     * @param   string  $message  error message
     * @param   string  $file     filename that the error was raised in
     * @param   int     $line     line number the error was raised at
     * @param   array   $context  array of every variable that existed in the scope the error was triggered in
     * @return  bool    true if error is supressable, else false
     */
    public function isSupressable(
            int $level,
            string $message,
            string $file = null,
            int $line = null,
            array $context = []
    ): bool
    {
        foreach ($this->errorHandlers as $errorHandler) {
            if ($errorHandler->isSupressable($level, $message, $file, $line, $context) == false) {
                return false;
            }
        }

        return true;
    }

    /**
     * handles the given error
     *
     * @param   int     $level    level of the raised error
     * @param   string  $message  error message
     * @param   string  $file     filename that the error was raised in
     * @param   int     $line     line number the error was raised at
     * @param   array   $context  array of every variable that existed in the scope the error was triggered in
     * @return  bool    true if error message should populate $php_errormsg, else false
     */
    public function handle(
            int $level,
            string $message,
            string $file = null,
            int $line = null,
            array $context = []
    ): bool
    {
        $errorReporting = error_reporting();
        foreach ($this->errorHandlers as $errorHandler) {
            if ($errorHandler->isResponsible($level, $message, $file, $line, $context)) {
                // if function/method was called with prepended @ and error is supressable
                if (0 == $errorReporting && $errorHandler->isSupressable($level, $message, $file, $line, $context)) {
                    return ErrorHandler::STOP_ERROR_HANDLING;
                }

                return $errorHandler->handle($level, $message, $file, $line, $context);
            }
        }

        return ErrorHandler::CONTINUE_WITH_PHP_INTERNAL_HANDLING;
    }
}
