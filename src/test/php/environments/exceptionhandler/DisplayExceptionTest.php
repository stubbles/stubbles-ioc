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
namespace stubbles\environments\exceptionhandler;
use bovigo\callmap\NewInstance;

use function bovigo\callmap\verify;
/**
 * Tests for stubbles\environments\exceptionhandler\DisplayExceptionHandler.
 *
 * @group  environments
 * @group  environments_exceptionhandler
 */
class DisplayExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * creates instance to test
     *
     * @return  \stubbles\environments\exceptionhandler\DisplayExceptionHandler
     */
    public function createExceptionHandler($sapi)
    {
        $displayExceptionHandler = NewInstance::of(
                DisplayException::class,
                ['/tmp', $sapi]
        )->mapCalls(['header' => false, 'writeBody' => false]);
        return $displayExceptionHandler->disableLogging();
    }

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
    public function writesMessageAndTraceForInternalException($throwable)
    {
        $displayExceptionHandler = $this->createExceptionHandler('cgi');
        $displayExceptionHandler->handleException($throwable);
        verify($displayExceptionHandler, 'header')
                ->received('Status: 500 Internal Server Error');
        verify($displayExceptionHandler, 'writeBody')
                ->received("failure message\nTrace:\n" . $throwable->getTraceAsString());
    }
}
