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
     * injected tire instance
     *
     * @var  Tire
     */
    public $tire;
    /**
     * @var  string
     */
    public $other;

    /**
     * sets the tire
     *
     * @param  Tire  $tire
     */
    public function __construct(Tire $tire, string $other = 'foo')
    {
        $this->tire  = $tire;
        $this->other = $other;
    }

    /**
     * moves the vehicle forward
     *
     * @return  string
     */
    public function moveForward()
    {
        return $this->tire->rotate();
    }
}
