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
use stubbles\test\ioc\Boss;
use stubbles\test\ioc\DevelopersMultipleConstructorParams;
use stubbles\test\ioc\DevelopersMultipleConstructorParamsGroupedName;
use stubbles\test\ioc\DevelopersMultipleConstructorParamsWithConstant;
use stubbles\test\ioc\Employee;
use stubbles\test\ioc\TeamMember;

use function bovigo\assert\assertThat;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\equals;
/**
 * Test for stubbles\ioc with @Named annotation.
 */
#[Group('ioc')]
class InjectorNamedTest extends TestCase
{
    #[Test]
    public function namedBindingIsKnownWhenSpecified(): void
    {
        $binder = new Binder();
        $binder->bind(Employee::class)->named('schst')->to(Boss::class);
        $injector = $binder->getInjector();
        assertTrue($injector->hasBinding(Employee::class, 'schst'));
    }

    #[Test]
    public function namedBindingIsNotUsedWhenNoGenericBindingSpecified(): void
    {
        $binder = new Binder();
        $binder->bind(Employee::class)->named('schst')->to(Boss::class);
        $injector = $binder->getInjector();
        assertFalse($injector->hasBinding(Employee::class));
    }

    #[Test]
    public function namedConstructorInjectionWithMultipleParamAndOneNamedParam(): void
    {
        $binder = new Binder();
        $binder->bind(Employee::class)->named('schst')->to(Boss::class);
        $binder->bind(Employee::class)->to(TeamMember::class);
        $injector = $binder->getInjector();
        $group = $injector->getInstance(DevelopersMultipleConstructorParams::class);
        assertThat(
            $group,
            equals(
                new DevelopersMultipleConstructorParams(
                    new Boss(),
                    new TeamMember()
                )
            )
        );
    }

    #[Test]
    public function namedConstructorInjectionWithMultipleParamAndOneNamedConstantParam(): void
    {
        $binder = new Binder();
        $binder->bindConstant('boss')->to('role:boss');
        $binder->bind(Employee::class)->to(TeamMember::class);
        $injector = $binder->getInjector();
        $group = $injector->getInstance(DevelopersMultipleConstructorParamsWithConstant::class);
        assertThat(
            $group,
            equals(
                new DevelopersMultipleConstructorParamsWithConstant(
                    new TeamMember(),
                    'role:boss'
                )
            )
        );
    }

    #[Test]
    public function namedConstructorInjectionWithMultipleParamAndNamedParamGroup(): void
    {
        $binder = new Binder();
        $binder->bind(Employee::class)->named('schst')->to(Boss::class);
        $binder->bind(Employee::class)->to(TeamMember::class);
        $injector = $binder->getInjector();
        $group = $injector->getInstance(DevelopersMultipleConstructorParamsGroupedName::class);
        assertThat(
            $group,
            equals(
                new DevelopersMultipleConstructorParamsGroupedName(
                    new Boss(),
                    new Boss()
                )
            )
        );
    }
}
