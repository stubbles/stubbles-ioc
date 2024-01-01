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
use stubbles\test\ioc\Number;
use stubbles\test\ioc\Random;
use stubbles\test\ioc\RandomSingleton;
use stubbles\test\ioc\SlotMachine;

use function bovigo\assert\assertThat;
use function bovigo\assert\predicate\isInstanceOf;
use function bovigo\assert\predicate\isSameAs;
/**
 * Test for stubbles\ioc\Injector with the singleton scope.
 */
#[Group('ioc')]
class InjectorSingletonScopeTest extends TestCase
{
    #[Test]
    public function assigningSingletonScopeToBindingWillReuseInitialInstance(): void
    {
        $binder = new Binder();
        $binder->bind(Number::class)
            ->to(Random::class)
            ->asSingleton();
        $slot = $binder->getInjector()->getInstance(SlotMachine::class);
        assertThat(
            $slot->number1,
            isInstanceOf(Random::class)->and(isSameAs($slot->number2))
        );
    }

    /**
     * @since  2.1.0
     */
    #[Test]
    #[Group('issue_31')]
    public function assigningSingletonScopeToClosureBindingWillReuseInitialInstance(): void
    {
        $binder = new Binder();
        $binder->bind(Number::class)
            ->toClosure(fn() => new Random())
            ->asSingleton();
        $slot = $binder->getInjector()->getInstance(SlotMachine::class);
        assertThat(
            $slot->number1,
            isInstanceOf(Random::class)->and(isSameAs($slot->number2))
        );
    }

    #[Test]
    public function classAnnotatedWithSingletonWillOnlyBeCreatedOnce(): void
    {
        $binder = new Binder();
        $binder->bind(Number::class)->to(RandomSingleton::class);
        $slot = $binder->getInjector()->getInstance(SlotMachine::class);
        assertThat(
            $slot->number1,
            isInstanceOf(RandomSingleton::class)->and(isSameAs($slot->number2))
        );
    }
}
