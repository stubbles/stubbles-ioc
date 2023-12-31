<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\environments\errorhandler;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertThat;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\equals;
/**
 * Tests for stubbles\environments\errorhandler\LogErrorHandler.
 */
#[Group('environments')]
#[Group('environments_errorhandler')]
class LogErrorHandlerTest extends TestCase
{
    private LogErrorHandler $logErrorHandler;
    private vfsStreamDirectory $root;
    private static string $logPath;
    private static string $logFile;

    public static function setUpBeforeClass(): void
    {
        self::$logPath = 'log/errors/' . date('Y') . '/' . date('m');
        self::$logFile = 'php-error-' . date('Y-m-d') . '.log';
    }

    protected function setUp(): void
    {
        $this->root            = vfsStream::setup();
        $this->logErrorHandler = new LogErrorHandler(vfsStream::url('root'));
    }

    #[Test]
    public function isAlwaysResponsible(): void
    {
        assertTrue($this->logErrorHandler->isResponsible(E_NOTICE, 'foo'));
    }

    #[Test]
    public function isNeverSupressable(): void
    {
        assertFalse($this->logErrorHandler->isSupressable(E_NOTICE, 'foo'));
    }

    #[Test]
    public function stopsErrorHandlingWhenHandled(): void
    {
        assertThat(
            $this->logErrorHandler->handle(E_WARNING, 'message', __FILE__, __LINE__),
            equals(ErrorHandler::STOP_ERROR_HANDLING)
        );
    }

    #[Test]
    public function handleErrorCreatesLogfile(): void
    {
        $this->logErrorHandler->handle(E_WARNING, 'message', __FILE__, __LINE__);
        assertTrue($this->root->hasChild(self::$logPath . '/' . self::$logFile));
    }

    #[Test]
    public function handleErrorShouldLogTheError(): void
    {
        $line = __LINE__;
        $this->logErrorHandler->handle(E_WARNING, 'message', __FILE__, $line);
        /** @var  \org\bovigo\vfs\vfsStreamFile  $logfile */
        $logfile = $this->root->getChild(self::$logPath . '/' . self::$logFile);
        assertThat(
            substr($logfile->getContent(), 19),
            equals(
                '|'
                . E_WARNING
                .
                '|E_WARNING|message|'
                . __FILE__
                .
                '|'
                . $line
                . "|no request uri present\n"
            )
        );
    }

    /**
     * @since  10.2.0
     */
    #[Test]
    #[Group('log_request_uri')]
    public function handleErrorShouldLogTheErrorWithRequestUriWhenPresent(): void
    {
        $_SERVER['REQUEST_URI'] = '/some/path?query=param';
        $_SERVER['HTTP_HOST']   = 'localhost';
        unset($_SERVER['HTTPS']);
        $_SERVER['SERVER_PORT'] = '8080';
        $line = __LINE__;
        $this->logErrorHandler->handle(E_WARNING, 'message', __FILE__, $line);
        /** @var  \org\bovigo\vfs\vfsStreamFile  $logfile */
        $logfile = $this->root->getChild(self::$logPath . '/' . self::$logFile);
        assertThat(
            substr($logfile->getContent(), 19),
            equals(
                '|'
                . E_WARNING
                . '|E_WARNING|message|'
                . __FILE__
                . '|'
                . $line
                . "|http://localhost:8080/some/path?query=param\n"
            )
        );
    }

    /**
     * @since  10.2.0
     */
    #[Test]
    #[Group('log_request_uri')]
    public function handleErrorShouldLogTheErrorWithRequestUriWhenPresentNoPortButHttps(): void
    {
        $_SERVER['REQUEST_URI'] = '/some/path?query=param';
        $_SERVER['HTTP_HOST']   = 'localhost';
        $_SERVER['HTTPS'] = 1;
        unset($_SERVER['SERVER_PORT']);
        $line = __LINE__;
        $this->logErrorHandler->handle(E_WARNING, 'message', __FILE__, $line);
        /** @var  \org\bovigo\vfs\vfsStreamFile  $logfile */
        $logfile = $this->root->getChild(self::$logPath . '/' . self::$logFile);
        assertThat(
            substr($logfile->getContent(), 19),
            equals(
                '|'
                . E_WARNING
                . '|E_WARNING|message|'
                . __FILE__
                . '|'
                . $line
                . "|https://localhost/some/path?query=param\n"
            )
        );
    }

    #[Test]
    public function handleShouldCreateLogDirectoryWithDefaultPermissionsIfNotExists(): void
    {
        $this->logErrorHandler->handle(E_WARNING, 'message', __FILE__, __LINE__);
        assertThat(
            $this->root->getChild(self::$logPath)->getPermissions(),
            equals(0700)
        );
    }

    #[Test]
    public function handleShouldCreateLogDirectoryWithChangedPermissionsIfNotExists(): void
    {
        $this->logErrorHandler->setFilemode(0777)
            ->handle(E_WARNING, 'message', __FILE__, __LINE__);
        assertThat(
            $this->root->getChild(self::$logPath)->getPermissions(),
            equals(0777)
        );
    }
}
