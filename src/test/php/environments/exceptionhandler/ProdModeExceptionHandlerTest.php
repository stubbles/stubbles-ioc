<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\environments\exceptionhandler;
use bovigo\callmap\NewInstance;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;

use function bovigo\callmap\verify;
/**
 * Tests for stubbles\environments\exceptionhandler\ProdModeExceptionHandler.
 *
 * @group  environments
 * @group  environments_exceptionhandler
 */
class ProdModeExceptionHandlerTest extends TestCase
{
    /**
     * root path for log files
     *
     * @var  \org\bovigo\vfs\vfsStreamDirectory
     */
    protected $root;

    protected function setUp(): void
    {
        $this->root = vfsStream::setup();
    }

    /**
     * creates instance to test
     *
     * @return  ProdModeExceptionHandler&\bovigo\callmap\ClassProxy
     */
    public function createExceptionHandler(string $sapi): ProdModeExceptionHandler
    {
        $prodModeExceptionHandler = NewInstance::of(
            ProdModeExceptionHandler::class,
            [vfsStream::url('root'), $sapi]
        )->stub('header', 'writeBody');
        $prodModeExceptionHandler->disableLogging();
        return $prodModeExceptionHandler;
    }

    /**
     * @return  array<\Throwable[]>
     */
    public static function throwables(): array
    {
        return [
                [new \Exception('failure message')],
                [new \Error('failure message')]
        ];
    }

    /**
     * @test
     * @dataProvider  throwables
     */
    public function createsFallbackErrorMessageIfNoError500FilePresent(\Throwable $throwable): void
    {
        $prodModeExceptionHandler = $this->createExceptionHandler('cgi');
        $prodModeExceptionHandler->handleException($throwable);
        verify($prodModeExceptionHandler, 'header')
                ->received('Status: 500 Internal Server Error');
        verify($prodModeExceptionHandler, 'writeBody')
                ->received('I\'m sorry but I can not fulfill your request. Somewhere someone messed something up.');
    }

    /**
     * @test
     * @dataProvider  throwables
     */
    public function returnsContentOfError500FileIfPresent(\Throwable $throwable): void
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
