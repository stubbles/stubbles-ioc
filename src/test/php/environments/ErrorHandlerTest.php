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
use stubbles\environments\errorhandler\ErrorHandler;

use function bovigo\assert\assert;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\isInstanceOf;
use function bovigo\assert\predicate\isSameAs;
/**
 * Tests for stubbles\environments\Handler.
 *
 * Contains all tests which require restoring the previous error handler.
 *
 * @group  environments
 */
class ErrorHandlerTest extends \PHPUnit_Framework_TestCase
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

            public function useErrorHandler($class)
            {
                return $this->setErrorHandler($class, 'handle');
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
        restore_error_handler();
    }

    /**
     * @test
     */
    public function registerErrorHandlerWithInvalidClassThrowsIllegalArgumentException()
    {
        expect(function() {
                $this->environment->useErrorHandler(404);
        })->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function registerErrorHandlerWithClassNameReturnsCreatedInstance()
    {
        $errorHandlerClass = NewInstance::classname(ErrorHandler::class);
        assert(
                $this->environment->useErrorHandler($errorHandlerClass)
                        ->registerErrorHandler('/tmp'),
                isInstanceOf($errorHandlerClass)
        );
    }

    /**
     * @test
     */
    public function registerErrorHandlerWithInstanceReturnsGivenInstance()
    {
        $errorHandler = NewInstance::of(ErrorHandler::class);
        assert(
                $this->environment->useErrorHandler($errorHandler)
                        ->registerErrorHandler('/tmp'),
                isSameAs($errorHandler)
        );
    }
}
