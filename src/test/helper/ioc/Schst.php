<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\test\ioc;
/**
 * The default implementation.
 */
class Schst implements Person, Person2, Person3
{
    /**
     * a method
     */
    public function sayHello()
    {
        return "My name is schst.";
    }

    public function sayHello2()
    {
        return 'My name is still schst.';
    }
}
