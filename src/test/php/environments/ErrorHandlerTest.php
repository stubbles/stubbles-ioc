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
use stubbles\environments\errorhandler\ErrorHandler;

use function bovigo\assert\assertThat;
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
class ErrorHandlerTest extends TestCase
{
    /**
     * instance to test
     *
     * @var  \stubbles\Environment
     */
    protected $environment;

    protected function setUp(): void
    {
        $this->environment = new class() extends Handler implements Environment
        {
            /**
             * @param   class-string<ErrorHandler>|ErrorHandler  $class
             * @return  self
             */
            public function useErrorHandler($class): self
            {
                $this->setErrorHandler($class, 'handle');
                return $this;
            }

            public function name(): string { return 'TEST'; }

            public function isCacheEnabled(): bool { return false; }
        };
    }

    protected function tearDown(): void
    {
        restore_error_handler();
    }

    /**
     * @test
     */
    public function registerErrorHandlerWithInvalidClassThrowsIllegalArgumentException(): void
    {
        expect(function() {
                $this->environment->useErrorHandler(404);
        })->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     */
    public function registerErrorHandlerWithClassNameReturnsCreatedInstance(): void
    {
        $errorHandlerClass = NewInstance::classname(ErrorHandler::class);
        assertThat(
                $this->environment->useErrorHandler($errorHandlerClass)
                        ->registerErrorHandler('/tmp'),
                isInstanceOf($errorHandlerClass)
        );
    }

    /**
     * @test
     */
    public function registerErrorHandlerWithInstanceReturnsGivenInstance(): void
    {
        $errorHandler = NewInstance::of(ErrorHandler::class);
        assertThat(
                $this->environment->useErrorHandler($errorHandler)
                        ->registerErrorHandler('/tmp'),
                isSameAs($errorHandler)
        );
    }
}
