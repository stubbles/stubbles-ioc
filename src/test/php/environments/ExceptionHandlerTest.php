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
namespace stubbles\environments;
use bovigo\callmap\NewInstance;
use stubbles\Environment;
use stubbles\environments\exceptionhandler\ExceptionHandler;

use function bovigo\assert\assert;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\isInstanceOf;
use function bovigo\assert\predicate\isSameAs;
/**
 * Tests for stubbles\environments\Handler.
 *
 * Contains all tests which require restoring the previous exception handler.
 *
 * @group   environments
 */
class ExceptionHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * instance to test
     *
     * @type  \stubbles\Environment
     */
    protected $environment;

    /**
     * set up test environment
     */
    public function setUp()
    {
        $this->environment = new class() implements Environment
        {
            use Handler;

            public function useExceptionHandler($class)
            {
                return $this->setExceptionHandler($class, 'handleException');
            }

            public function name(): string { return 'TEST'; }

            public function isCacheEnabled(): bool { return false; }
        };
    }

    /**
     * clean up test environment
     */
    public function tearDown()
    {
        restore_exception_handler();
    }

    /**
     * @test
     */
    public function registerExceptionHandlerWithInvalidClassThrowsIllegalArgumentException()
    {
        expect(function() {
                $this->environment->useExceptionHandler(404);
        })->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function registerExceptionHandlerWithClassNameReturnsCreatedInstance()
    {
        $exceptionHandlerClass = NewInstance::classname(ExceptionHandler::class);
        assert(
                $this->environment->useExceptionHandler($exceptionHandlerClass)
                        ->registerExceptionHandler('/tmp'),
                isInstanceOf($exceptionHandlerClass)
        );
    }

    /**
     * @test
     */
    public function registerExceptionHandlerWithInstanceReturnsGivenInstance()
    {
        $exceptionHandler = NewInstance::of(ExceptionHandler::class);
        assert(
                $this->environment->useExceptionHandler($exceptionHandler)
                        ->registerExceptionHandler('/tmp'),
                isSameAs($exceptionHandler)
        );
    }
}
