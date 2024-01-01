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
 * @since  5.1.0
 */
class BikeWithOptionalOtherParam implements Vehicle
{
    /**
     * sets the tire
     *
     * @param  Tire  $tire
     */
    public function __construct(public Tire $tire, public string $other = 'foo') { }

    /**
     * moves the vehicle forward
     */
    public function moveForward(): string
    {
        return $this->tire->rotate();
    }
}
