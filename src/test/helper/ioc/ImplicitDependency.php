<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\test\ioc;
/**
 * Helper class to test implicit binding with concrete class names.
 */
class ImplicitDependency
{
    /**
     * constructor
     *
     * @param  Goodyear  $goodyear
     */
    public function __construct(private Goodyear $goodyear) { }

    /**
     * returns the instance from constructor injection
     *
     * @return  Goodyear
     */
    public function getGoodyearByConstructor()
    {
        return $this->goodyear;
    }
}
