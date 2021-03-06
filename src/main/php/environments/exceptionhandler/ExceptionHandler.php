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
namespace stubbles\environments\exceptionhandler;
/**
 * Interface for exception handlers.
 *
 * @see  http://php.net/set_exception_handler
 */
interface ExceptionHandler
{
    /**
     * handles the exception
     *
     * @param  \Throwable  $exception  the uncatched exception
     */
    public function handleException(\Throwable $exception): void;
}
