<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\test\ioc;
/**
 * One more helper class for the test.
 */
class SlotMachine
{
    public function __construct(public Number $number1, public Number $number2) { }
}
