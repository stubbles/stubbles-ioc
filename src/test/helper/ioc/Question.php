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
 * Helper class for ioc tests.
 */
class Question
{
    /**
     * sets the answer
     */
    #[Named('answer')]
    public function __construct(private mixed $answer) { }

    /**
     * returns the answer
     *
     * @return  mixed
     */
    public function getAnswer(): mixed
    {
        return $this->answer;
    }
}
