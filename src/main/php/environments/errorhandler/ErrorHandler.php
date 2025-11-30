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
 * Interface for PHP error handlers.
 *
 * @see  http://php.net/set_error_handler
 */
interface ErrorHandler
{
    /**
     * constant to signal that php's internal error handling should take over
     */
    public const bool CONTINUE_WITH_PHP_INTERNAL_HANDLING = false;
    /**
     * constant to signal error handling should be stopped
     */
    public const bool STOP_ERROR_HANDLING                 = true;

    /**
     * checks whether this error handler is responsible for the given error
     *
     * @param   int     $level    level of the raised error
     * @param   string  $message  error message
     * @param   string  $file     filename that the error was raised in
     * @param   int     $line     line number the error was raised at
     * @return  bool    true if error handler is responsible, else false
     */
    public function isResponsible(
        int $level,
        string $message,
        ?string $file = null,
        ?int $line = null
    ): bool;

    /**
     * checks whether this error is supressable
     *
     * This method is called in case the level is 0. It decides whether the
     * error has to be handled or if it can be omitted.
     *
     * @param   int     $level    level of the raised error
     * @param   string  $message  error message
     * @param   string  $file     filename that the error was raised in
     * @param   int     $line     line number the error was raised at
     * @return  bool    true if error is supressable, else false
     */
    public function isSupressable(
        int $level,
        string $message,
        ?string $file = null,
        ?int $line = null
    ): bool;

    /**
     * handles the given error
     *
     * @param   int     $level    level of the raised error
     * @param   string  $message  error message
     * @param   string  $file     filename that the error was raised in
     * @param   int     $line     line number the error was raised at
     * @return  bool    true if error message should populate $php_errormsg, else false
     */
    public function handle(
        int $level,
        string $message,
        ?string $file = null,
        ?int $line = null
    ): bool;
}
