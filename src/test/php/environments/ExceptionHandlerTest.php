<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\environments;
use bovigo\callmap\NewInstance;
use PHPUnit\Framework\TestCase;
use stubbles\Environment;
use stubbles\environments\exceptionhandler\ExceptionHandler;

use function bovigo\assert\assertThat;
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
class ExceptionHandlerTest extends TestCase
{
    /**
     * instance to test
     *
     * @var  \stubbles\Environment
     */
    protected $environment;

    protected function setUp(): void
    {
        $this->environment = new class() implements Environment
        {
            use Handler;

            /**
             * @param   class-string<ExceptionHandler>|ExceptionHandler  $class
             * @return  Environment
             */
            public function useExceptionHandler($class): Environment
            {
                return $this->setExceptionHandler($class, 'handleException');
            }

            public function name(): string { return 'TEST'; }

            public function isCacheEnabled(): bool { return false; }
        };
    }

    protected function tearDown(): void
    {
        restore_exception_handler();
    }

    /**
     * @test
     */
    public function registerExceptionHandlerWithInvalidClassThrowsIllegalArgumentException(): void
    {
        expect(function() {
                $this->environment->useExceptionHandler(404);
        })->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function registerExceptionHandlerWithClassNameReturnsCreatedInstance(): void
    {
        $exceptionHandlerClass = NewInstance::classname(ExceptionHandler::class);
        assertThat(
                $this->environment->useExceptionHandler($exceptionHandlerClass)
                        ->registerExceptionHandler('/tmp'),
                isInstanceOf($exceptionHandlerClass)
        );
    }

    /**
     * @test
     */
    public function registerExceptionHandlerWithInstanceReturnsGivenInstance(): void
    {
        $exceptionHandler = NewInstance::of(ExceptionHandler::class);
        assertThat(
                $this->environment->useExceptionHandler($exceptionHandler)
                        ->registerExceptionHandler('/tmp'),
                isSameAs($exceptionHandler)
        );
    }
}
