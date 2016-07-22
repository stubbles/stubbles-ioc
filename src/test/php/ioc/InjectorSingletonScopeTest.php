<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles
 */
namespace stubbles\ioc;
use stubbles\test\ioc\Number;
use stubbles\test\ioc\Random;
use stubbles\test\ioc\RandomSingleton;
use stubbles\test\ioc\SlotMachine;

use function bovigo\assert\assert;
use function bovigo\assert\predicate\isInstanceOf;
use function bovigo\assert\predicate\isSameAs;
/**
 * Test for stubbles\ioc\Injector with the singleton scope.
 *
 * @group  ioc
 */
class InjectorSingletonScopeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function assigningSingletonScopeToBindingWillReuseInitialInstance()
    {
        $binder = new Binder();
        $binder->bind(Number::class)
               ->to(Random::class)
               ->asSingleton();
        $slot = $binder->getInjector()->getInstance(SlotMachine::class);
        assert(
                $slot->number1,
                isInstanceOf(Random::class)->and(isSameAs($slot->number2))
        );
    }

    /**
     * @since  2.1.0
     * @test
     * @group  issue_31
     */
    public function assigningSingletonScopeToClosureBindingWillReuseInitialInstance()
    {
        $binder = new Binder();
        $binder->bind(Number::class)
                ->toClosure(function() { return new Random(); })
                ->asSingleton();
        $slot = $binder->getInjector()->getInstance(SlotMachine::class);
        assert(
                $slot->number1,
                isInstanceOf(Random::class)->and(isSameAs($slot->number2))
        );
    }

    /**
     * @test
     */
    public function classAnnotatedWithSingletonWillOnlyBeCreatedOnce()
    {
        $binder = new Binder();
        $binder->bind(Number::class)->to(RandomSingleton::class);
        $slot = $binder->getInjector()->getInstance(SlotMachine::class);
        assert(
                $slot->number1,
                isInstanceOf(RandomSingleton::class)->and(isSameAs($slot->number2))
        );
    }
}
