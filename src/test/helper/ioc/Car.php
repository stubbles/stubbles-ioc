<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\test\ioc;
/**
 * Another helper class for injection and binding tests.
 */
class Car implements Vehicle
{
    /**
     * Create a new car
     *
     * @param  Tire  $tire
     */
    public function __construct(public Tire $tire) { }

    /**
     * moves the vehicle forward
     */
    public function moveForward(): string
    {
        return $this->tire->rotate();
    }
}
