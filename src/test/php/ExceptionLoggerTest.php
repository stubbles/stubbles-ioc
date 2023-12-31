<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles;

use Exception;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stubbles\test\environments\ThrowablesDataProvider;
use Throwable;

use function bovigo\assert\assertThat;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\equals;
use function stubbles\reflect\annotationsOf;
use function stubbles\reflect\annotationsOfConstructor;
/**
 * Tests for stubbles\ExceptionLogger.
 *
 * @since  3.3.0
 */
#[Group('app')]
class ExceptionLoggerTest extends TestCase
{
    private ExceptionLogger $exceptionLogger;
    private vfsStreamDirectory $root;
    private static string $logPath;
    private static string $logFile;

    public static function setUpBeforeClass(): void
    {
        self::$logPath = 'log/errors/' . date('Y') . '/' . date('m');
        self::$logFile = 'exceptions-' . date('Y-m-d') . '.log';
    }

    protected function setUp(): void
    {
        $this->root            = vfsStream::setup();
        $this->exceptionLogger = new ExceptionLogger(vfsStream::url('root'));
    }

    /**
     * @since  5.4.0
     */
    #[Test]
    public function annotationsPresentOnClass(): void
    {
        assertTrue(annotationsOf($this->exceptionLogger)->contain('Singleton'));
    }

    /**
     * @since  3.3.1
     */
    #[Test]
    public function annotationsPresentOnConstructor(): void
    {
        $annotations = annotationsOfConstructor($this->exceptionLogger);
        assertTrue($annotations->contain('Named'));
        assertThat(
                $annotations->named('Named')[0]->getName(),
                equals('stubbles.project.path')
        );
    }

    #[Test]
    #[DataProviderExternal(ThrowablesDataProvider::class, 'throwables')]
    public function logsExceptionDataCreatesLogfile(Throwable $throwable): void
    {
        $this->exceptionLogger->log($throwable);
        assertTrue($this->root->hasChild(self::$logPath . '/' . self::$logFile));
    }

    #[Test]
    #[DataProviderExternal(ThrowablesDataProvider::class, 'throwables')]
    public function logsExceptionData(Throwable $throwable): void
    {
        $this->exceptionLogger->log($throwable);
        /** @var  \org\bovigo\vfs\vfsStreamFile  $logfile */
        $logfile = $this->root->getChild(self::$logPath . '/' . self::$logFile);
        assertThat(
            substr($logfile->getContent(), 19),
            equals(
                '|' . get_class($throwable) . '|failure message|'
                . ThrowablesDataProvider::file() . '|' . $throwable->getLine() . '|||||' . "\n"
            )
        );
    }

    /**
     * @since  10.2.0
     */
    #[Test]
    #[DataProviderExternal(ThrowablesDataProvider::class, 'throwables')]
    #[Group('log_request_id')]
    public function logsExceptionDataWithRequestId(Throwable $throwable): void
    {
        $this->exceptionLogger->log($throwable, 'some-request-id');
        /** @var  \org\bovigo\vfs\vfsStreamFile  $logfile */
        $logfile = $this->root->getChild(self::$logPath . '/' . self::$logFile);
        assertThat(
            substr($logfile->getContent(), 19),
            equals(
                '|' . get_class($throwable) . '|failure message|'
                . ThrowablesDataProvider::file() . '|' . $throwable->getLine() . "|||||some-request-id\n"
            )
        );
    }

    #[Test]
    #[DataProviderExternal(ThrowablesDataProvider::class, 'throwables')]
    public function logsExceptionDataOfChainedAndCause(Throwable $throwable): void
    {
        $exception = new Exception('chained exception', 303, $throwable);
        $line      = __LINE__ - 1;
        $this->exceptionLogger->log($exception);
        /** @var  \org\bovigo\vfs\vfsStreamFile  $logfile */
        $logfile = $this->root->getChild(self::$logPath . '/' . self::$logFile);
        assertThat(
            substr($logfile->getContent(), 19),
            equals(
                '|Exception|chained exception|' . __FILE__ . '|' . $line
                . '|' . get_class($throwable) . '|failure message|'
                . ThrowablesDataProvider::file() . '|' . $throwable->getLine() . '|' . "\n"
            )
        );
    }

    #[Test]
    #[DataProviderExternal(ThrowablesDataProvider::class, 'throwables')]
    public function logsExceptionDataOfChainedAndCauseWithRequestId(Throwable $throwable): void
    {
        $exception = new Exception('chained exception', 303, $throwable);
        $line      = __LINE__ - 1;
        $this->exceptionLogger->log($exception, 'some-request-id');
        /** @var  \org\bovigo\vfs\vfsStreamFile  $logfile */
        $logfile = $this->root->getChild(self::$logPath . '/' . self::$logFile);
        assertThat(
            substr($logfile->getContent(), 19),
            equals(
                '|Exception|chained exception|' . __FILE__ . '|' . $line
                . '|' . get_class($throwable) . '|failure message|'
                . ThrowablesDataProvider::file() . '|' . $throwable->getLine() . "|some-request-id\n"
            )
        );
    }

    #[Test]
    #[DataProviderExternal(ThrowablesDataProvider::class, 'throwables')]
    public function createsLogDirectoryWithDefaultModeIfNotExists(Throwable $throwable): void
    {
        $this->exceptionLogger->log($throwable);
        assertThat(
                $this->root->getChild(self::$logPath)->getPermissions(),
                equals(0700)
        );
    }

    #[Test]
    #[DataProviderExternal(ThrowablesDataProvider::class, 'throwables')]
    public function createsLogDirectoryWithChangedModeIfNotExists(Throwable $throwable): void
    {
        $this->exceptionLogger->setFilemode(0777)->log($throwable);
        assertThat(
            $this->root->getChild(self::$logPath)->getPermissions(),
            equals(0777)
        );
    }
}
