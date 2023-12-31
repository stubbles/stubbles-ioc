<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\test\environments;

use stubbles\Environment;
use stubbles\environments\errorhandler\ErrorHandler;
use stubbles\environments\exceptionhandler\ExceptionHandler;
use stubbles\environments\Handler;

class TestEnvironment extends Handler implements Environment
{
    public function useErrorHandler(string|ErrorHandler $class): self
    {
        $this->setErrorHandler($class, 'handle');
        return $this;
    }

    public function useExceptionHandler(string|ExceptionHandler $class): self
    {
        $this->setExceptionHandler($class, 'handleException');
        return $this;
    }

    public function name(): string { return 'TEST'; }

    public function isCacheEnabled(): bool { return false; }
}
