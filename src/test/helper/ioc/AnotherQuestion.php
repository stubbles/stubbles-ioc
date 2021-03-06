<?php
declare(strict_types=1);
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
class AnotherQuestion
{
    /**
     * answer
     *
     * @var  Answer
     */
    private $answer;

    /**
     * @param  Answer  $answer
     * @Named('answer')
     */
    public function __construct(Answer $answer)
    {
        $this->answer = $answer;
    }

    /**
     * returns answer
     *
     * @return  Answer
     */
    public function getAnswer()
    {
        return $this->answer;
    }
}
