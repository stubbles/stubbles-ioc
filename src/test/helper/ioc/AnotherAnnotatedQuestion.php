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
 *
 * @deprecated will be removed with 13.0.0
 */
class AnotherAnnotatedQuestion
{
    /**
     * @Named('answer')
     */
    public function __construct(private Answer $answer) { }

    public function getAnswer(): Answer
    {
        return $this->answer;
    }
}
