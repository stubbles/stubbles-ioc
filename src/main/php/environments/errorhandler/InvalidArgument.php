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
 * Error handler for illegal arguments.
 *
 * This error handler is responsible for errors of type E_RECOVERABLE_ERROR which denote that
 * a type hint has been infringed with an argument of another type. If such an error is detected
 * an stubIllegalArgumentException will be thrown.
 *
 * @internal
 */
class InvalidArgument implements ErrorHandler
{
    /**
     * checks whether this error handler is responsible for the given error
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
        if (E_RECOVERABLE_ERROR != $level) {
            return false;
        }

        return (bool) preg_match('/Argument [0-9]+ passed to [a-zA-Z0-9_\\\\]+::[a-zA-Z0-9_]+\(\) must be an instance of [a-zA-Z0-9_\\\\]+, [a-zA-Z0-9_\\\\]+ given/', $message);
    }

    /**
     * checks whether this error is supressable
     *
     * This method is called in case the level is 0. A type hint infringement
     * is never supressable.
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
        return false;
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
     * @throws  \InvalidArgumentException
     */
    public function handle(
            int $level,
            string $message,
            string $file = null,
            int $line = null,
            array $context = []
    ): bool
    {
        throw new \InvalidArgumentException(
                $message . ' @ ' . $file . ' on line ' . $line
        );
    }
}
