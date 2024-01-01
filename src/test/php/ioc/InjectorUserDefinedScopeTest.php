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
use stdClass;
use stubbles\ioc\binding\BindingScope;

use function bovigo\assert\assertThat;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\isSameAs;
/**
 * Test for stubbles\ioc\Injector with user-defined scope.
 */
#[Group('ioc')]
class InjectorUserDefinedScopeTest extends TestCase
{
    #[Test]
    public function hasBindingWhenBoundToOtherScope(): void
    {
        $binder = new Binder();
        $binder->bind(stdClass::class)
            ->to(stdClass::class)
            ->in(NewInstance::of(BindingScope::class));
        assertTrue($binder->getInjector()->hasBinding(stdClass::class));
    }

    #[Test]
    public function otherScopeIsUsedToCreateInstance(): void
    {
        $binder   = new Binder();
        $instance = new stdClass();
        $binder->bind(stdClass::class)
            ->to(stdClass::class)
            ->in(
                NewInstance::of(BindingScope::class)
                    ->returns(['getInstance' => $instance])
            );
        assertThat(
            $binder->getInjector()->getInstance(\stdClass::class),
            isSameAs($instance)
        );
    }
}
