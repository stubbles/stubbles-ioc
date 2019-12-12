<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\environments\errorhandler;
use bovigo\callmap\NewInstance;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertThat;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\equals;
use function bovigo\callmap\verify;
/**
 * Tests for stubbles\environments\errorhandler\ErrorHandlers
 *
 * @group  environments
 * @group  environments_errorhandler
 */
class ErrorHandlersTest extends TestCase
{
    /**
     * instance to test
     *
     * @var  ErrorHandlers
     */
    protected $errorHandlers;
    /**
     * a mocked error handler
     *
     * @var  ErrorHandler&\bovigo\callmap\ClassProxy
     */
    protected $errorHandler1;
    /**
     * a mocked error handler
     *
     * @var  ErrorHandler&\bovigo\callmap\ClassProxy
     */
    protected $errorHandler2;
    /**
     * a mocked error handler
     *
     * @var  ErrorHandler&\bovigo\callmap\ClassProxy
     */
    protected $errorHandler3;

    protected function setUp(): void
    {
        $this->errorHandlers = new ErrorHandlers();
        $this->errorHandler1 = NewInstance::of(ErrorHandler::class);
        $this->errorHandlers->addErrorHandler($this->errorHandler1);
        $this->errorHandler2 = NewInstance::of(ErrorHandler::class);
        $this->errorHandlers->addErrorHandler($this->errorHandler2);
        $this->errorHandler3 = NewInstance::of(ErrorHandler::class);
        $this->errorHandlers->addErrorHandler($this->errorHandler3);
    }

    /**
     * @test
     */
    public function isResponsibleDoesOnlyCallErrorHandlersUntilResponsibleOneFound(): void
    {
        $this->errorHandler1->returns(['isResponsible' => false]);
        $this->errorHandler2->returns(['isResponsible' => true]);
        assertTrue($this->errorHandlers->isResponsible(1, 'foo'));
        verify($this->errorHandler3, 'isResponsible')->wasNeverCalled();
     }

    /**
     * @test
     */
    public function isResponsibleReturnsFalseIfNoHandlerIsResponsible(): void
    {
        $this->errorHandler1->returns(['isResponsible' => false]);
        $this->errorHandler2->returns(['isResponsible' => false]);
        $this->errorHandler3->returns(['isResponsible' => false]);
        assertFalse($this->errorHandlers->isResponsible(1, 'foo'));
    }

    /**
     * @test
     */
    public function isSupressableReturnsFalseAsSoonAsOneHandlerDeniesSupressability(): void
    {
        $this->errorHandler1->returns(['isSupressable' => true]);
        $this->errorHandler2->returns(['isSupressable' => false]);
        assertFalse($this->errorHandlers->isSupressable(1, 'foo'));
        verify($this->errorHandler3, 'isSupressable')->wasNeverCalled();
    }

    /**
     * @test
     */
    public function isSupressableReturnsOnlyTrueIfAllHandlerAllowSupressability(): void
    {
        $this->errorHandler1->returns(['isSupressable' => true]);
        $this->errorHandler2->returns(['isSupressable' => true]);
        $this->errorHandler3->returns(['isSupressable' => true]);
        assertTrue($this->errorHandlers->isSupressable(1, 'foo'));
    }

    /**
     * @test
     */
    public function handleSignalsDefaultStrategyIfNoErrorHandlerIsResponsible(): void
    {
        $this->errorHandler1->returns(['isResponsible' => false]);
        $this->errorHandler2->returns(['isResponsible' => false]);
        $this->errorHandler3->returns(['isResponsible' => false]);
        assertThat(
                $this->errorHandlers->handle(1, 'foo'),
                equals(ErrorHandler::CONTINUE_WITH_PHP_INTERNAL_HANDLING)
        );
    }

    /**
     * @test
     */
    public function handleSignalsStopIfErrorIsSuppressableAndSuppressedByGlobalErrorReporting(): void
    {
        $oldLevel = error_reporting(0);
        try {
            $this->errorHandler1->returns(['isResponsible' => false]);
            $this->errorHandler2->returns(
                    ['isResponsible' => true, 'isSupressable' => true]
            );
            assertThat(
                    $this->errorHandlers->handle(1, 'foo'),
                    equals(ErrorHandler::STOP_ERROR_HANDLING)
            );
        } finally {
            error_reporting($oldLevel);
        }
    }

    /**
     * @test
     */
    public function handleSignalsResultOfResponsibleErrorHandlerIfErrorReportingDisabled(): void
    {
        $oldLevel = error_reporting(0);
        try {
            $this->errorHandler1->returns(['isResponsible' => false]);
            $this->errorHandler2->returns(
                    ['isResponsible' => true,
                     'isSupressable' => false,
                     'handle'        => ErrorHandler::STOP_ERROR_HANDLING
                    ]
            );
            assertThat(
                    $this->errorHandlers->handle(1, 'foo'),
                    equals(ErrorHandler::STOP_ERROR_HANDLING)
            );
            verify($this->errorHandler3, 'isResponsible')->wasNeverCalled();
        } finally {
            error_reporting($oldLevel);
        }
    }

    /**
     * @test
     */
    public function handleSignalsResultOfResponsibleErrorHandlerIfErrorReportingEnabled(): void
    {
        $oldLevel = error_reporting(E_ALL);
        try {
            $this->errorHandler1->returns(['isResponsible' => false]);
            $this->errorHandler2->returns(
                    ['isResponsible' => true,
                     'isSupressable' => false,
                     'handle'        => ErrorHandler::STOP_ERROR_HANDLING
                    ]
            );
            assertThat(
                    $this->errorHandlers->handle(1, 'foo'),
                    equals(ErrorHandler::STOP_ERROR_HANDLING)
            );
        } finally {
            error_reporting($oldLevel);
        }
    }
}
