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

use Override;
use Throwable;

/**
 * Exception handler for production mode: fills the response with an error document.
 *
 * @internal
 */
class ProdModeExceptionHandler extends AbstractExceptionHandler
{
    #[Override]
    protected function createResponseBody(Throwable $exception): string
    {
        if (file_exists($this->projectPath . '/docroot/500.html')) {
            $content = file_get_contents($this->projectPath . '/docroot/500.html');
            if (false !== $content) {
              return $content;
            }
        }

        return "I'm sorry but I can not fulfill your request. Somewhere someone messed something up.";
    }
}
