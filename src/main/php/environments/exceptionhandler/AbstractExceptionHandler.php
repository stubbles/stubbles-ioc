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
use stubbles\ExceptionLogger;
use Throwable;

/**
 * Abstract base implementation for exception handlers, containing logging of exceptions.
 *
 * @internal
 */
abstract class AbstractExceptionHandler implements ExceptionHandler
{
    private bool $loggingEnabled = true;
    private ExceptionLogger $exceptionLogger;

    public function __construct(protected string $projectPath, private string $sapi = PHP_SAPI)
    {
        $this->exceptionLogger = new ExceptionLogger($projectPath);
    }

    /**
     * disables exception logging
     */
    public function disableLogging(): AbstractExceptionHandler
    {
        $this->loggingEnabled = false;
        return $this;
    }

    /**
     * sets the mode for new log directories
     */
    public function setFilemode(int $filemode): AbstractExceptionHandler
    {
        $this->exceptionLogger->setFilemode($filemode);
        return $this;
    }

    public function handleException(Throwable $exception): void
    {
        if ($this->loggingEnabled) {
            $this->exceptionLogger->log($exception, $this->requestUri());
        }

        if ('cgi' === $this->sapi) {
            $this->header('Status: 500 Internal Server Error');
        } else {
            $this->header('HTTP/1.1 500 Internal Server Error');
        }

        $this->writeBody($this->createResponseBody($exception));
    }

    private function requestUri(): string
    {
        if (!isset($_SERVER['REQUEST_URI'])) {
            return 'no request uri present';
        }

        return (isset($_SERVER['HTTPS']) ? 'https' : 'http')
            . '://'
            . ($_SERVER['HTTP_HOST'] ?? '')
            . (isset($_SERVER['SERVER_PORT']) ? ':' . $_SERVER['SERVER_PORT'] : '')
            . $_SERVER['REQUEST_URI'];
    }

    /**
     * creates response body with useful data for display
     */
    abstract protected function createResponseBody(Throwable $exception): string;

    /**
     * helper method to send the header
     */
    protected function header(string $header): void
    {
        header($header);
    }

    /**
     * helper method to send the body
     */
    protected function writeBody(string $body): void
    {
        echo $body;
        flush();
    }
}
