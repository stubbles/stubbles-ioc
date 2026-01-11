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
 * @ImplementedBy(stubbles\test\ioc\Schst.class)
 * @deprecated will be removed with 13.0.0
 */
interface PersonAnnotated
{
    /**
     * a method
     */
    public function sayHello(): string;
}
