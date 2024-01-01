<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\test\ioc;
/**
 * Helper class for the test.
 */
class Boss implements Employee
{
    /**
     * says hello
     */
    public function sayHello(): string
    {
        return "hello team member";
    }
}
