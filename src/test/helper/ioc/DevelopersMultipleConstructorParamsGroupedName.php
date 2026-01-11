<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\test\ioc;

use stubbles\ioc\attributes\Named;

/**
 * Helper class for the test.
 */
class DevelopersMultipleConstructorParamsGroupedName
{
    /**
     * constructor method with Named() annotation for all parameters
     */
    #[Named('schst')]
    public function __construct(public Employee $boss, public Employee $employee)  { }
}
