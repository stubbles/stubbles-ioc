<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\test\ioc;
/**
 * Helper class to test implicit binding related to bug #102.
 */
class ImplicitOptionalDependency
{
    /**
     * constructor
     *
     * @param  Goodyear  $goodyear
     */
    public function __construct(protected ?Goodyear $goodyear = null) { }

    /**
     * returns the instance from implicit optional injection
     */
    public function getGoodyear(): ?Goodyear
    {
        return $this->goodyear;
    }
}
