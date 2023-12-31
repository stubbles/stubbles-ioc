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
use stubbles\environments\errorhandler\ErrorHandler;
use stubbles\test\environments\TestEnvironment;

use function bovigo\assert\assertThat;
use function bovigo\assert\predicate\isInstanceOf;
use function bovigo\assert\predicate\isSameAs;
/**
 * Tests for stubbles\environments\Handler.
 *
 * Contains all tests which require restoring the previous error handler.
 */
#[Group('environments')]
class ErrorHandlerTest extends TestCase
{
    private TestEnvironment $environment;

    protected function setUp(): void
    {
        $this->environment = new TestEnvironment();
    }

    protected function tearDown(): void
    {
        restore_error_handler();
    }

    #[Test]
    public function registerErrorHandlerWithClassNameReturnsCreatedInstance(): void
    {
        $errorHandlerClass = NewInstance::classname(ErrorHandler::class);
        assertThat(
            $this->environment->useErrorHandler($errorHandlerClass)
                ->registerErrorHandler('/tmp'),
            isInstanceOf($errorHandlerClass)
        );
    }

    #[Test]
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
