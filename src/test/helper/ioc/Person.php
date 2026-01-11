<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\test\ioc;

use stubbles\ioc\attributes\ImplementedBy;

/**
 * Interface with ImplementedBy attribute.
 */
#[ImplementedBy(Schst::class)]
interface Person
{
    /**
     * a method
     */
    public function sayHello(): string;
}
