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
namespace stubbles\test\ioc;
use stubbles\ioc\InjectionProvider;
/**
 * Helper class for ioc tests.
 *
 * @since  1.6.0
 */
class AnswerConstantProvider implements InjectionProvider
{
    /**
     * returns the value to provide
     *
     * @param   string  $name  optional
     * @return  mixed
     */
    public function get(string $name = null)
    {
        return 42;
    }
}
