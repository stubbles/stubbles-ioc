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

use function bovigo\callmap\verify;
/**
 * Tests for stubbles\environments\exceptionhandler\DisplayException.
 *
 * @group  environments
 * @group  environments_exceptionhandler
 */
class DisplayExceptionTest extends TestCase
{
    /**
     * creates instance to test
     *
     * @return  DisplayException&\bovigo\callmap\ClassProxy
     */
    public function createExceptionHandler(string $sapi): DisplayException
    {
        $displayExceptionHandler = NewInstance::of(
                DisplayException::class,
                ['/tmp', $sapi]
        )->returns(['header' => false, 'writeBody' => false]);
        $displayExceptionHandler->disableLogging();
        return $displayExceptionHandler;
    }

    /**
     * @return  array<\Throwable[]>
     */
    public function throwables(): array
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
    public function writesMessageAndTraceForInternalException(\Throwable $throwable): void
    {
        $displayExceptionHandler = $this->createExceptionHandler('cgi');
        $displayExceptionHandler->handleException($throwable);
        verify($displayExceptionHandler, 'header')
                ->received('Status: 500 Internal Server Error');
        verify($displayExceptionHandler, 'writeBody')
                ->received("failure message\nTrace:\n" . $throwable->getTraceAsString());
    }
}
