<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles
 */
namespace stubbles\environments\exceptionhandler;
use bovigo\callmap\NewInstance;
use org\bovigo\vfs\vfsStream;

use function bovigo\assert\assert;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\equals;
use function bovigo\callmap\verify;
/**
 * Tests for stubbles\environments\exceptionhandler\AbstractExceptionHandler.
 *
 * @group  environments
 * @group  environments_exceptionhandler
 */
class AbstractExceptionHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @type  \stubbles\environments\exceptionhandler\AbstractExceptionHandler
     */
    private $exceptionHandler;
    /**
     * root path for log files
     *
     * @type  org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;


    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->root             = vfsStream::setup();
        $this->exceptionHandler = NewInstance::of(
                AbstractExceptionHandler::class,
                [vfsStream::url('root')]
        )->mapCalls([
                'header'             => null,
                'createResponseBody' => null,
                'writeBody'          => null
        ]);
    }

    /**
     * @return  array
     */
    public function throwables()
    {
        $throwables = [[new \Exception('failure message')]];
        if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
            $throwables[] = [new \Error('failure message')];
        }

        return $throwables;
    }

    /**
     * @test
     * @dataProvider  throwables
     */
    public function loggingDisabledDoesNotCreateLogfile($throwable)
    {
        $this->exceptionHandler->disableLogging()
                ->handleException($throwable);
        assertFalse(
                $this->root->hasChild(
                        'log/errors/' . date('Y') . '/' . date('m')
                        . '/exceptions-' . date('Y-m-d') . '.log'
                )
        );
    }

    /**
     * @test
     * @dataProvider  throwables
     */
    public function loggingNotDisabledCreatesLogfile($throwable)
    {
        $this->exceptionHandler->handleException($throwable);
        assertTrue(
                $this->root->hasChild(
                        'log/errors/' . date('Y') . '/' . date('m')
                        . '/exceptions-' . date('Y-m-d') . '.log'
                )
        );
    }

    /**
     * @test
     * @dataProvider  throwables
     */
    public function loggingDisabledFillsResponseOnly($throwable)
    {
        $this->exceptionHandler->disableLogging()
                ->handleException($throwable);
        verify($this->exceptionHandler, 'header')->wasCalledOnce();
        verify($this->exceptionHandler, 'createResponseBody')->wasCalledOnce();
        verify($this->exceptionHandler, 'writeBody')->wasCalledOnce();
    }

    /**
     * @test
     * @dataProvider  throwables
     */
    public function handleExceptionLogsExceptionData($throwable)
    {
        $this->exceptionHandler->handleException($throwable);
        $line = __LINE__ - 1;
        assert(
                substr(
                        $this->root->getChild(
                                'log/errors/' . date('Y') . '/' . date('m')
                                . '/exceptions-' . date('Y-m-d') . '.log'
                        )->getContent(),
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
    public function handleChainedExceptionLogsExceptionDataOfChainedAndCause($throwable)
    {
        $exception = new \Exception('chained exception', 303, $throwable);
        $line      = __LINE__ - 1;
        $this->exceptionHandler->handleException($exception);
        assert(
                substr(
                        $this->root->getChild(
                                'log/errors/' . date('Y') . '/' . date('m')
                                . '/exceptions-' . date('Y-m-d') . '.log'
                        )->getContent(),
                        19
                ),
                equals(
                        '|Exception|chained exception|'
                        . __FILE__ . '|' . $line . '|' . get_class($throwable) . '|failure message|'
                        . __FILE__ . '|' . $throwable->getLine() . "\n"
                )
        );
    }

    /**
     * @test
     * @dataProvider  throwables
     */
    public function createsLogDirectoryWithDefaultPermissionsIfNotExists($throwable)
    {
        $this->exceptionHandler->handleException($throwable);
        assert(
                $this->root->getChild(
                        'log/errors/' . date('Y') . '/' . date('m')
                )->getPermissions(),
                equals(0700)
        );
    }

    /**
     * @test
     * @dataProvider  throwables
     */
    public function createLogDirectoryWithChangedPermissionsIfNotExists($throwable)
    {
        $this->exceptionHandler->setFilemode(0777)->handleException($throwable);
        assert(
                $this->root->getChild(
                        'log/errors/' . date('Y') . '/' . date('m')
                )->getPermissions(),
                equals(0777)
        );
    }
}
