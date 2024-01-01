<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\test\ioc;
/**
 * Class that is marked as Singleton
 *
 * @Singleton
 */
class RandomSingleton implements Number
{
    /**
     * value of the number
     */
    private int $value;

    /**
     * constructor
     */
    public function __construct()
    {
        srand();
        $this->value = rand(0, 5000);
    }

    /**
     * display a number
     */
    public function display(): void
    {
        echo $this->value . "\n";
    }
}
