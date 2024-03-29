<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\test\ioc;
use stubbles\ioc\InjectionProvider;
/**
 * Helper class for the test.
 */
class MyProviderClass implements InjectionProvider
{
    /**
     * returns the value to provide
     */
    public function get(string $name = null): mixed
    {
        return new Answer();
    }
}
