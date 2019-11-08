<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\test\ioc;
/**
 * Interface with annotation.
 *
 * @since  6.0.0
 * @ImplementedBy(environment="DEV", class=stubbles\test\ioc\Mikey.class)
 * @ImplementedBy(stubbles\test\ioc\Schst.class)
 */
interface Person3
{
    /**
     * a method
     */
    public function sayHello();
}
