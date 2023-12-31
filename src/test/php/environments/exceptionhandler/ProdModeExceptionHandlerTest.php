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
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use stubbles\test\environments\ThrowablesDataProvider;
use Throwable;

use function bovigo\callmap\verify;
/**
 * Tests for stubbles\environments\exceptionhandler\ProdModeExceptionHandler.
 */
#[Group('environments')]
#[Group('environments_exceptionhandler')]
class ProdModeExceptionHandlerTest extends TestCase
{
    private vfsStreamDirectory $root;

    protected function setUp(): void
    {
        $this->root = vfsStream::setup();
    }

    public function createExceptionHandler(string $sapi): ProdModeExceptionHandler&ClassProxy
    {
        $prodModeExceptionHandler = NewInstance::of(
            ProdModeExceptionHandler::class,
            [vfsStream::url('root'), $sapi]
        )->stub('header', 'writeBody');
        $prodModeExceptionHandler->disableLogging();
        return $prodModeExceptionHandler;
    }

    #[Test]
    #[DataProviderExternal(ThrowablesDataProvider::class, 'throwables')]
    public function createsFallbackErrorMessageIfNoError500FilePresent(Throwable $throwable): void
    {
        $prodModeExceptionHandler = $this->createExceptionHandler('cgi');
        $prodModeExceptionHandler->handleException($throwable);
        verify($prodModeExceptionHandler, 'header')
            ->received('Status: 500 Internal Server Error');
        verify($prodModeExceptionHandler, 'writeBody')
            ->received('I\'m sorry but I can not fulfill your request. Somewhere someone messed something up.');
    }

    #[Test]
    #[DataProviderExternal(ThrowablesDataProvider::class, 'throwables')]
    public function returnsContentOfError500FileIfPresent(Throwable $throwable): void
    {
        vfsStream::newFile('docroot/500.html')
            ->withContent('An error occurred')
            ->at($this->root);
        $prodModeExceptionHandler = $this->createExceptionHandler('apache');
        $prodModeExceptionHandler->handleException($throwable);
        verify($prodModeExceptionHandler, 'header')
            ->received('HTTP/1.1 500 Internal Server Error');
        verify($prodModeExceptionHandler, 'writeBody')
            ->received('An error occurred');
    }
}
