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
 * Interface with attribute.
 *
 * @since  6.0.0
 */
#[ImplementedBy(Mikey::class, environment:'DEV')]
interface Person4
{
    /**
     * a method
     */
    public function sayHello(): string;
}
