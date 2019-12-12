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
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertThat;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\equals;
/**
 * Tests for stubbles\environments\errorhandler\LogErrorHandler.
 *
 * @group  environments
 * @group  environments_errorhandler
 */
class LogErrorHandlerTest extends TestCase
{
    /**
     * instance to test
     *
     * @var  \stubbles\environments\errorhandler\LogErrorHandler
     */
    private $logErrorHandler;
    /**
     * root path for log files
     *
     * @var  \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;
    /**
     * @var  string
     */
    private static $logPath;
    /**
     * @var  string
     */
    private static $logFile;

    /**
     * set up test environment
     */
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

    /**
     * @test
     */
    public function isAlwaysResponsible(): void
    {
        assertTrue($this->logErrorHandler->isResponsible(E_NOTICE, 'foo'));
    }

    /**
     * @test
     */
    public function isNeverSupressable(): void
    {
        assertFalse($this->logErrorHandler->isSupressable(E_NOTICE, 'foo'));
    }

    /**
     * @test
     */
    public function stopsErrorHandlingWhenHandled(): void
    {
        assertThat(
            $this->logErrorHandler->handle(E_WARNING, 'message', __FILE__, __LINE__),
            equals(ErrorHandler::STOP_ERROR_HANDLING)
        );
    }

    /**
     * @test
     */
    public function handleErrorCreatesLogfile(): void
    {
        $this->logErrorHandler->handle(E_WARNING, 'message', __FILE__, __LINE__);
        assertTrue($this->root->hasChild(self::$logPath . '/' . self::$logFile));
    }

    /**
     * @test
     */
    public function handleErrorShouldLogTheError(): void
    {
        $line = __LINE__;
        $this->logErrorHandler->handle(E_WARNING, 'message', __FILE__, $line);
        /** @var  \org\bovigo\vfs\vfsStreamFile  $logfile */
        $logfile = $this->root->getChild(self::$logPath . '/' . self::$logFile);
        assertThat(
            substr($logfile->getContent(), 19),
            equals('|' . E_WARNING . '|E_WARNING|message|' . __FILE__ . '|' . $line . "\n")
        );
    }

    /**
     * @test
     */
    public function handleShouldCreateLogDirectoryWithDefaultPermissionsIfNotExists(): void
    {
        $this->logErrorHandler->handle(E_WARNING, 'message', __FILE__, __LINE__);
        assertThat(
            $this->root->getChild(self::$logPath)->getPermissions(),
            equals(0700)
        );
    }

    /**
     * @test
     */
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
