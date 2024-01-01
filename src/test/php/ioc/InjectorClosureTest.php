<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\ioc;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stubbles\test\ioc\AnotherQuestion;
use stubbles\test\ioc\Answer;

use function bovigo\assert\assertThat;
use function bovigo\assert\predicate\isInstanceOf;
use function bovigo\assert\predicate\isSameAs;
/**
 * Test for stubbles\ioc\Injector with closure binding.
 *
 * @since  2.1.0
 */
#[Group('ioc')]
#[Group('issue_31')]
class InjectorClosureTest extends TestCase
{
    #[Test]
    public function injectWithClosure(): void
    {
        $binder = new Binder();
        $answer = new Answer();
        $binder->bind(Answer::class)->toClosure(fn() => $answer);
        $question = $binder->getInjector()->getInstance(AnotherQuestion::class);
        assertThat($question, isInstanceOf(AnotherQuestion::class));
        assertThat($question->getAnswer(), isSameAs($answer));
    }
}
