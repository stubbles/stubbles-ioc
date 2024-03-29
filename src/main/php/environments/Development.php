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
use stubbles\environments\exceptionhandler\DisplayException;
/**
 * Represents development environment.
 *
 * Cache is disabled, and erors as well as exceptions will be displayed.
 *
 * @api
 * @since  7.0.0
 */
class Development extends Handler implements Environment
{
    public function __construct()
    {
        $this->setExceptionHandler(DisplayException::class);
    }

    /**
     * returns the name of the mode
     */
    public function name(): string
    {
        return 'DEV';
    }

    /**
     * checks whether cache is enabled or not
     *
     * @return  bool
     */
    public function isCacheEnabled(): bool
    {
        return Environment::CACHE_DISABLED;
    }
}
