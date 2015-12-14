<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles
 */
namespace stubbles\lang\errorhandler;
use function bovigo\callmap\verify;
use bovigo\callmap\NewInstance;
/**
 * Tests for stubbles\lang\errorhandler\DisplayExceptionHandler.
 *
 * @group  lang
 * @group  lang_errorhandler
 */
class DisplayExceptionHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * creates instance to test
     *
     * @return  \stubbles\lang\errorhandler\DisplayExceptionHandler
     */
    public function createExceptionHandler($sapi)
    {
        $displayExceptionHandler = NewInstance::of(
                DisplayExceptionHandler::class,
                ['/tmp', $sapi]
        )->mapCalls(['header' => false, 'writeBody' => false]);
        return $displayExceptionHandler->disableLogging();
    }

    /**
     * @test
     */
    public function writesMessageAndTraceForInternalException()
    {
        $exception = new \Exception('message');
        $displayExceptionHandler = $this->createExceptionHandler('cgi');
        $displayExceptionHandler->handleException($exception);
        verify($displayExceptionHandler, 'header')
                ->received('Status: 500 Internal Server Error');
        verify($displayExceptionHandler, 'writeBody')
                ->received("message\nTrace:\n" . $exception->getTraceAsString());
    }
}
