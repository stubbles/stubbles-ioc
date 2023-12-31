<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\environments\exceptionhandler;

use bovigo\callmap\ClassProxy;
use bovigo\callmap\NewInstance;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stubbles\test\environments\ThrowablesDataProvider;
use Throwable;

use function bovigo\callmap\verify;
/**
 * Tests for stubbles\environments\exceptionhandler\DisplayException.
 */
#[Group('environments')]
#[Group('environments_exceptionhandler')]
class DisplayExceptionTest extends TestCase
{
    private function createExceptionHandler(string $sapi): DisplayException&ClassProxy
    {
        $displayExceptionHandler = NewInstance::of(
            DisplayException::class,
            ['/tmp', $sapi]
        )->stub('header', 'writeBody');
        $displayExceptionHandler->disableLogging();
        return $displayExceptionHandler;
    }

    #[Test]
    #[DataProviderExternal(ThrowablesDataProvider::class, 'throwables')]
    public function writesMessageAndTraceForInternalException(Throwable $throwable): void
    {
        $displayExceptionHandler = $this->createExceptionHandler('cgi');
        $displayExceptionHandler->handleException($throwable);
        verify($displayExceptionHandler, 'header')
            ->received('Status: 500 Internal Server Error');
        verify($displayExceptionHandler, 'writeBody')
            ->received("failure message\nTrace:\n" . $throwable->getTraceAsString());
    }
}
