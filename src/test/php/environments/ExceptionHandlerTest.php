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
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stubbles\environments\exceptionhandler\ExceptionHandler;
use stubbles\test\environments\TestEnvironment;

use function bovigo\assert\assertThat;
use function bovigo\assert\predicate\isInstanceOf;
use function bovigo\assert\predicate\isSameAs;
/**
 * Tests for stubbles\environments\Handler.
 *
 * Contains all tests which require restoring the previous exception handler.
 */
#[Group('environments')]
class ExceptionHandlerTest extends TestCase
{
    private TestEnvironment $environment;

    protected function setUp(): void
    {
        $this->environment = new TestEnvironment();
    }

    protected function tearDown(): void
    {
        restore_exception_handler();
    }

    #[Test]
    public function registerExceptionHandlerWithClassNameReturnsCreatedInstance(): void
    {
        $exceptionHandlerClass = NewInstance::classname(ExceptionHandler::class);
        assertThat(
            $this->environment->useExceptionHandler($exceptionHandlerClass)
                ->registerExceptionHandler('/tmp'),
            isInstanceOf($exceptionHandlerClass)
        );
    }

    #[Test]
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
