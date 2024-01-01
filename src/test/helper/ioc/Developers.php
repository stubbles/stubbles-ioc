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
class Developers
{
    /**
     * Setter method with Named() annotation
     *
     * @Named{schst}('schst')
     */
    public function __construct(public Employee $schst, public Employee $mikey) { }
}
