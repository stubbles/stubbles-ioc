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
namespace stubbles;
use org\bovigo\vfs\vfsStream;

use function bovigo\assert\assert;
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
class ExceptionLoggerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @type  \stubbles\ExceptionLogger
     */
    private $exceptionLogger;
    /**
     * root path for log files
     *
     * @type  org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;
    /**
     * @type  string
     */
    private static $logPath;
    /**
     * @type  string
     */
    private static $logFile;

    /**
     * set up test environment
     */
    public static function setUpBeforeClass()
    {
        self::$logPath = 'log/errors/' . date('Y') . '/' . date('m');
        self::$logFile = 'exceptions-' . date('Y-m-d') . '.log';
    }

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->root            = vfsStream::setup();
        $this->exceptionLogger = new ExceptionLogger(vfsStream::url('root'));
    }

    /**
     * @test
     * @since  5.4.0
     */
    public function annotationsPresentOnClass()
    {
        assertTrue(annotationsOf($this->exceptionLogger)->contain('Singleton'));
    }

    /**
     * @test
     * @since  3.3.1
     */
    public function annotationsPresentOnConstructor()
    {
        $annotations = annotationsOfConstructor($this->exceptionLogger);
        assertTrue($annotations->contain('Named'));
        assert(
                $annotations->named('Named')[0]->getName(),
                equals('stubbles.project.path')
        );
    }

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
    public function logsExceptionDataCreatesLogfile($throwable)
    {
        $this->exceptionLogger->log($throwable);
        assertTrue($this->root->hasChild(self::$logPath . '/' . self::$logFile));
    }

    /**
     * @test
     * @dataProvider  throwables
     */
    public function logsExceptionData($throwable)
    {
        $this->exceptionLogger->log($throwable);
        $line = __LINE__ - 1;
        assert(
                substr(
                        $this->root->getChild(self::$logPath . '/' . self::$logFile)
                                ->getContent(),
                        19
                ),
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
    public function logsExceptionDataOfChainedAndCause($throwable)
    {
        $exception = new \Exception('chained exception', 303, $throwable);
        $line      = __LINE__ - 1;
        $this->exceptionLogger->log($exception);
        assert(
                substr(
                        $this->root->getChild(self::$logPath . '/' . self::$logFile)
                                ->getContent(),
                        19
                ),
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
    public function createsLogDirectoryWithDefaultModeIfNotExists($throwable)
    {
        $this->exceptionLogger->log($throwable);
        assert(
                $this->root->getChild(self::$logPath)->getPermissions(),
                equals(0700)
        );
    }

    /**
     * @test
     * @dataProvider  throwables
     */
    public function createsLogDirectoryWithChangedModeIfNotExists($throwable)
    {
        $this->exceptionLogger->setFilemode(0777)->log($throwable);
        assert(
                $this->root->getChild(self::$logPath)->getPermissions(),
                equals(0777)
        );
    }
}
