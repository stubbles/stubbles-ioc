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

use Override;
use Throwable;

/**
 * Exception handler that displays the exception message nicely formated in the response.
 *
 * You should not use this exception handler in production mode!
 *
 * @internal
 */
class DisplayException extends AbstractExceptionHandler
{
    #[Override]
    protected function createResponseBody(Throwable $exception): string
    {
        return $exception->getMessage()
            . "\nTrace:\n" . $exception->getTraceAsString();
    }
}
