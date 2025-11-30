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
 * Error handler that logs all errors.
 *
 * This error handler logs all errors that occured. In a composition of error
 * handlers it should be added as last one so it catches all errors that have
 * not been handled before.
 *
 * @internal
 */
class LogErrorHandler implements ErrorHandler
{
    /**
     * list of error levels and their string representation
     *
     * @var  string[]
     */
    private const array LEVEL_STRINGS = [
        E_ERROR             => 'E_ERROR',
        E_WARNING           => 'E_WARNING',
        E_PARSE             => 'E_PARSE',
        E_NOTICE            => 'E_NOTICE',
        E_CORE_ERROR        => 'E_CORE_ERROR',
        E_CORE_WARNING      => 'E_CORE_WARNING',
        E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
        E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
        E_USER_ERROR        => 'E_USER_ERROR',
        E_USER_WARNING      => 'E_USER_WARNING',
        E_USER_NOTICE       => 'E_USER_NOTICE',
        // use raw number to prevent deprecation warning
        2048                => 'E_STRICT',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        E_DEPRECATED        => 'E_DEPRECATED',
        E_USER_DEPRECATED   => 'E_USER_DEPRECATED',
        E_ALL               => 'E_ALL',
    ];
    /**
     * directory to log errors into
     */
    private string $logDir;
    /**
     * mode for new directories
     */
    private int $filemode  = 0700;

    public function __construct(string $projectPath)
    {
        $this->logDir = $projectPath . DIRECTORY_SEPARATOR . 'log'
                . DIRECTORY_SEPARATOR . 'errors' . DIRECTORY_SEPARATOR
                . '{Y}' . DIRECTORY_SEPARATOR . '{M}';
    }

    /**
     * sets the mode for new log directories
     */
    public function setFilemode($filemode): LogErrorHandler
    {
        $this->filemode = $filemode;
        return $this;
    }

    /**
     * checks whether this error handler is responsible for the given error
     *
     * This error handler is always responsible.
     */
    #[Override]
    public function isResponsible(
        int $level,
        string $message,
        ?string $file = null,
        ?int $line = null
    ): bool {
        return true;
    }

    /**
     * checks whether this error is supressable
     *
     * This method is called in case the level is 0. An error to log is never
     * supressable.
     */
    #[Override]
    public function isSupressable(
        int $level,
        string $message,
        ?string $file = null,
        ?int $line = null
    ): bool {
        return false;
    }

    #[Override]
    public function handle(
        int $level,
        string $message,
        ?string $file = null,
        ?int $line = null
    ): bool {
        $logData  = date('Y-m-d H:i:s') . '|' . $level;
        $logData .= '|' . (self::LEVEL_STRINGS[$level] ?? 'unknown');
        $logData .= '|' . $message;
        $logData .= '|' . $file;
        $logData .= '|' . $line;
        $logData .= '|' . $this->requestUri();
        $logDir   = $this->buildLogDir();
        if (!file_exists($logDir)) {
            mkdir($logDir, $this->filemode, true);
        }

        error_log(
            $logData . "\n",
            3,
            $logDir . DIRECTORY_SEPARATOR . 'php-error-' . date('Y-m-d') . '.log'
        );
        return ErrorHandler::STOP_ERROR_HANDLING;
    }

    private function buildLogDir(): string
    {
        return str_replace('{Y}', date('Y'), str_replace('{M}', date('m'), $this->logDir));
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
}
