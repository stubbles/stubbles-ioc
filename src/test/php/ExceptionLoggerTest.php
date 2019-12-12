<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertThat;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\equals;
use function stubbles\reflect\annotationsOf;
use function stubbles\reflect\annotationsOfConstructor;
/**
 * Tests for stubbles\ExceptionLogger.
 *
 * @group  app
 * @since  3.3.0
 */
class ExceptionLoggerTest extends TestCase
{
    /**
     * instance to test
     *
     * @var  \stubbles\ExceptionLogger
     */
    private $exceptionLogger;
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
        self::$logFile = 'exceptions-' . date('Y-m-d') . '.log';
    }

    protected function setUp(): void
    {
        $this->root            = vfsStream::setup();
        $this->exceptionLogger = new ExceptionLogger(vfsStream::url('root'));
    }

    /**
     * @test
     * @since  5.4.0
     */
    public function annotationsPresentOnClass(): void
    {
        assertTrue(annotationsOf($this->exceptionLogger)->contain('Singleton'));
    }

    /**
     * @test
     * @since  3.3.1
     */
    public function annotationsPresentOnConstructor(): void
    {
        $annotations = annotationsOfConstructor($this->exceptionLogger);
        assertTrue($annotations->contain('Named'));
        assertThat(
                $annotations->named('Named')[0]->getName(),
                equals('stubbles.project.path')
        );
    }

    /**
     * @return  array<\Throwable[]>
     */
    public function throwables(): array
    {
        return [
                [new \Exception('failure message')],
                [new \Error('failure message')]
        ];
    }

    /**
     * @test
     * @dataProvider  throwables
     */
    public function logsExceptionDataCreatesLogfile(\Throwable $throwable): void
    {
        $this->exceptionLogger->log($throwable);
        assertTrue($this->root->hasChild(self::$logPath . '/' . self::$logFile));
    }

    /**
     * @test
     * @dataProvider  throwables
     */
    public function logsExceptionData(\Throwable $throwable): void
    {
        $this->exceptionLogger->log($throwable);
        $line = __LINE__ - 1;
        /** @var  \org\bovigo\vfs\vfsStreamFile  $logfile */
        $logfile = $this->root->getChild(self::$logPath . '/' . self::$logFile);
        assertThat(
            substr($logfile->getContent(), 19),
            equals(
                '|' . get_class($throwable) . '|failure message|'
                . __FILE__ . '|' . $throwable->getLine() . "||||\n"
            )
        );

    }

    /**
     * @test
     * @dataProvider  throwables
     */
    public function logsExceptionDataOfChainedAndCause(\Throwable $throwable): void
    {
        $exception = new \Exception('chained exception', 303, $throwable);
        $line      = __LINE__ - 1;
        $this->exceptionLogger->log($exception);
        /** @var  \org\bovigo\vfs\vfsStreamFile  $logfile */
        $logfile = $this->root->getChild(self::$logPath . '/' . self::$logFile);
        assertThat(
            substr($logfile->getContent(), 19),
            equals(
                '|Exception|chained exception|' . __FILE__ . '|' . $line
                . '|' . get_class($throwable) . '|failure message|'
                . __FILE__ . '|' . $throwable->getLine() . "\n"
            )
        );
    }

    /**
     * @test
     * @dataProvider  throwables
     */
    public function createsLogDirectoryWithDefaultModeIfNotExists(\Throwable $throwable): void
    {
        $this->exceptionLogger->log($throwable);
        assertThat(
                $this->root->getChild(self::$logPath)->getPermissions(),
                equals(0700)
        );
    }

    /**
     * @test
     * @dataProvider  throwables
     */
    public function createsLogDirectoryWithChangedModeIfNotExists(\Throwable $throwable): void
    {
        $this->exceptionLogger->setFilemode(0777)->log($throwable);
        assertThat(
                $this->root->getChild(self::$logPath)->getPermissions(),
                equals(0777)
        );
    }
}
