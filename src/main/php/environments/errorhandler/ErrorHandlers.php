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

use Override;

/**
 * Container for a collection of PHP error handlers.
 */
class ErrorHandlers implements ErrorHandler
{
    /** @var  ErrorHandler[] */
    private array $errorHandlers = [];

    /**
     * adds an error handler to the collection
     */
    public function addErrorHandler(ErrorHandler $errorHandler): void
    {
        $this->errorHandlers[] = $errorHandler;
    }

    #[Override]
    public function isResponsible(
        int $level,
        string $message,
        string $file = null,
        int $line = null
    ): bool
    {
        foreach ($this->errorHandlers as $errorHandler) {
            if ($errorHandler->isResponsible($level, $message, $file, $line) == true) {
                return true;
            }
        }

        return false;
    }

    #[Override]
    public function isSupressable(
        int $level,
        string $message,
        string $file = null,
        int $line = null
    ): bool
    {
        foreach ($this->errorHandlers as $errorHandler) {
            if ($errorHandler->isSupressable($level, $message, $file, $line) == false) {
                return false;
            }
        }

        return true;
    }

    #[Override]
    public function handle(
        int $level,
        string $message,
        string $file = null,
        int $line = null
    ): bool
    {
        $errorReporting = error_reporting();
        foreach ($this->errorHandlers as $errorHandler) {
            if ($errorHandler->isResponsible($level, $message, $file, $line)) {
                // if function/method was called with prepended @ and error is supressable
                if (0 == $errorReporting && $errorHandler->isSupressable($level, $message, $file, $line)) {
                    return ErrorHandler::STOP_ERROR_HANDLING;
                }

                return $errorHandler->handle($level, $message, $file, $line);
            }
        }

        return ErrorHandler::CONTINUE_WITH_PHP_INTERNAL_HANDLING;
    }
}
