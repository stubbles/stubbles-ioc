<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\test\ioc;
/**
 * Helper class for optional constructor injection.
 *
 * @since  2.0.0
 */
class BikeWithOptionalTire implements Vehicle
{
    public Tire $tire;

    /**
     * sets the tire
     *
     * @param  Tire  $tire
     */
    public function __construct(?Tire $tire = null)
    {
        $this->tire = $tire ?? new Goodyear();
    }

    /**
     * moves the vehicle forward
     */
    public function moveForward(): string
    {
        return $this->tire->rotate();
    }
}
