<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\ioc;
use bovigo\callmap\NewInstance;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stubbles\ioc\binding\BindingException;
use stubbles\test\ioc\AnotherQuestion;
use stubbles\test\ioc\Answer;
use stubbles\test\ioc\MyProviderClass;

use function bovigo\assert\assertThat;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\isInstanceOf;
use function bovigo\assert\predicate\isSameAs;
use function bovigo\callmap\verify;
/**
 * Test for stubbles\ioc\Injector with provider binding.
 */
#[Group('ioc')]
class InjectorProviderTest extends TestCase
{
    #[Test]
    public function injectWithProviderInstance(): void
    {
        $binder   = new Binder();
        $answer   = new Answer();
        $provider = NewInstance::of(InjectionProvider::class)
            ->returns(['get' => $answer]);
        $binder->bind(Answer::class)->toProvider($provider);
        $question = $binder->getInjector()
            ->getInstance(AnotherQuestion::class);
        assertThat($question->getAnswer(), isSameAs($answer));
        verify($provider, 'get')->received('answer');
    }

    #[Test]
    public function injectWithInvalidProviderClassThrowsException(): void
    {
        $binder = new Binder();
        $binder->bind(Answer::class)->toProviderClass(\stdClass::class);
        $injector = $binder->getInjector();
        expect(function() use ($injector) {
            $injector->getInstance(AnotherQuestion::class);
        })->throws(BindingException::class);
    }

    #[Test]
    public function injectWithProviderClassName(): void
    {
        $binder = new Binder();
        $binder->bind(Answer::class)
            ->toProviderClass(MyProviderClass::class);
        $question = $binder->getInjector()
            ->getInstance(AnotherQuestion::class);
        assertThat($question->getAnswer(), isInstanceOf(Answer::class));
    }

    #[Test]
    public function injectWithProviderClass(): void
    {
        $binder = new Binder();
        $binder->bind(Answer::class)
            ->toProviderClass(
                new \ReflectionClass(MyProviderClass::class)
            );
        $question = $binder->getInjector()
            ->getInstance(AnotherQuestion::class);
        assertThat($question->getAnswer(), isInstanceOf(Answer::class));
    }
}
