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
namespace stubbles\environments;
use stubbles\Environment;
use stubbles\environments\errorhandler\LogErrorHandler;
use stubbles\environments\exceptionhandler\ProdModeExceptionHandler;
/**
 * Represents production environment.
 *
 * Cache is enabled, and both errors and exceptions will be logged and not
 * displayed.
 *
 * @api
 * @since  7.0.0
 */
class Production extends Handler implements Environment
{
    public function __construct()
    {
        $this->setExceptionHandler(ProdModeExceptionHandler::class)
            ->setErrorHandler(LogErrorHandler::class);
    }

    /**
     * returns the name of the mode
     */
    public function name(): string
    {
        return 'PROD';
    }

    /**
     * checks whether cache is enabled or not
     */
    public function isCacheEnabled(): bool
    {
        return Environment::CACHE_ENABLED;
    }
}
