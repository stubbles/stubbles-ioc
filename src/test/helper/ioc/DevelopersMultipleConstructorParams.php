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
class DevelopersMultipleConstructorParams
{
    /**
     * constructor with Named() annotation on a specific param
     *
     * @Named{boss}('schst')
     */
    public function __construct(public Employee $boss, public Employee $employee) { }
}
