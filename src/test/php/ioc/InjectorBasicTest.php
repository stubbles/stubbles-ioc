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
use stubbles\ioc\binding\BindingException;
use stubbles\test\ioc\Bike;
use stubbles\test\ioc\BikeWithOptionalOtherParam;
use stubbles\test\ioc\BikeWithOptionalTire;
use stubbles\test\ioc\Car;
use stubbles\test\ioc\Goodyear;
use stubbles\test\ioc\ImplicitDependency;
use stubbles\test\ioc\ImplicitOptionalDependency;
use stubbles\test\ioc\MissingArrayInjection;
use stubbles\test\ioc\Tire;
use stubbles\test\ioc\Vehicle;

use function bovigo\assert\assertThat;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertNull;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isInstanceOf;
/**
 * Test for stubbles\ioc\Injector.
 */
#[Group('ioc')]
class InjectorBasicTest extends TestCase
{
    #[Test]
    public function injectorHasBindingsWhenSpecified(): void
    {
        $injector = Binder::createInjector(
            function(Binder $binder)
            {
                $binder->bind(Tire::class)->to(Goodyear::class);
                $binder->bind(Vehicle::class)->to(Car::class);
            }
        );
        assertTrue($injector->hasBinding(Vehicle::class));
        assertTrue($injector->hasBinding(Tire::class));
    }

    #[Test]
    public function constructorInjection(): void
    {
        $injector = Binder::createInjector(
            function(Binder $binder)
            {
                $binder->bind(Tire::class)->to(Goodyear::class);
                $binder->bind(Vehicle::class)->to(Car::class);
            }
        );
        assertThat(
            $injector->getInstance(Vehicle::class),
            equals(new Car(new Goodyear()))
        );
    }

    #[Test]
    public function doesNotHaveExplicitBindingWhenNotDefined(): void
    {
        $injector = Binder::createInjector();
        assertFalse($injector->hasExplicitBinding(Goodyear::class));
    }

    #[Test]
    public function usesImplicitBindingViaTypehints(): void
    {
        $goodyear = Binder::createInjector()->getInstance(Goodyear::class);
        assertThat($goodyear, isInstanceOf(Goodyear::class));
    }

    #[Test]
    public function implicitBindingTurnsIntoExplicitBindingAfterFirstUsage(): void
    {
        $injector = Binder::createInjector();
        $injector->getInstance(Goodyear::class);
        assertTrue($injector->hasExplicitBinding(Goodyear::class));
    }

    #[Test]
    public function implicitBindingAsDependency(): void
    {
        $injector = Binder::createInjector();
        $obj      = $injector->getInstance(ImplicitDependency::class);
        assertThat($obj->getGoodyearByConstructor(), isInstanceOf(Goodyear::class));
    }

    #[Test]
    public function optionalImplicitDependencyWillNotBeSetIfNotBound(): void
    {
        $injector = Binder::createInjector();
        $obj      = $injector->getInstance(ImplicitOptionalDependency::class);
        assertNull($obj->getGoodyear());
    }

    #[Test]
    public function optionalImplicitDependencyWillBeSetIfBound(): void
    {
        $injector = Binder::createInjector(
                function(Binder $binder)
                {
                    $binder->bind(Goodyear::class)->to(Goodyear::class);
                }
        );
        $obj      = $injector->getInstance(ImplicitOptionalDependency::class);
        assertThat($obj->getGoodyear(), isInstanceOf(Goodyear::class));
    }

    #[Test]
    public function missingBindingThrowsBindingException(): void
    {
        $injector = Binder::createInjector();
        expect(function() use ($injector) {
                $injector->getInstance(Vehicle::class);
        })->throws(BindingException::class);
    }

    #[Test]
    public function missingBindingOnInjectionHandlingThrowsBindingException(): void
    {
        $injector = Binder::createInjector();
        expect(function() use ($injector) {
                $injector->getInstance(Bike::class);
        })->throws(BindingException::class);
    }

    #[Test]
    public function missingConstantBindingOnInjectionHandlingThrowsBindingException(): void
    {
        $injector = Binder::createInjector();
        expect(function() use ($injector) {
            $injector->getInstance(MissingArrayInjection::class);
        })->throws(BindingException::class);
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function optionalConstructorInjection(): void
    {
        $injector = Binder::createInjector();
        $bike     = $injector->getInstance(BikeWithOptionalTire::class);
        assertThat($bike->tire, isInstanceOf(Goodyear::class));
    }

    /**
     * @since  5.1.0
     */
    #[Test]
    public function constructorInjectionWithOptionalSecondParam(): void
    {
        $injector = Binder::createInjector(
            function(Binder $binder)
            {
                $binder->bind(Tire::class)->to(Goodyear::class);
            }
        );
        $bike = $injector->getInstance(BikeWithOptionalOtherParam::class);
        assertThat($bike->other, equals('foo'));
    }
}
