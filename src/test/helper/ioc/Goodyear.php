<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\test\ioc;

use Override;

/**
 * Helper class for injection and binding tests.
 */
class Goodyear implements Tire
{
    /**
     * rotates the tires
     */
    #[Override]
    public function rotate(): string
    {
        return "I'm driving with Goodyear tires.";
    }
}
