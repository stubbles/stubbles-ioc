<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\ioc\binding;

use bovigo\callmap\ClassProxy;
use bovigo\callmap\NewInstance;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;
use stubbles\ioc\InjectionProvider;
use stubbles\ioc\Injector;

use function bovigo\assert\assertThat;
use function bovigo\assert\assertEmptyArray;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
/**
 * Test for stubbles\ioc\binding\ListBinding.
 *
 * @since  2.0.0
 */
#[Group('ioc')]
#[Group('ioc_binding')]
class ListBindingTest extends TestCase
{
    private ListBinding $listBinding;
    private Injector&ClassProxy $injector;

    protected function setUp(): void
    {
        $this->injector    = NewInstance::of(Injector::class);
        $this->listBinding = new ListBinding('foo');
    }

    #[Test]
    public function getKeyReturnsUniqueListKey(): void
    {
        assertThat($this->listBinding->getKey(), equals(ListBinding::TYPE . '#foo'));
    }

    #[Test]
    public function returnsEmptyListIfNothingAdded(): void
    {
        assertEmptyArray($this->listBinding->getInstance($this->injector, 'int'));
    }

    #[Test]
    public function returnsTypedEmptyListIfNothingAdded(): void
    {
        assertEmptyArray(
            $this->listBinding->getInstance(
                $this->injector,
                new ReflectionClass(stdClass::class)
            )
        );
    }

    #[Test]
    public function valueIsAddedToList(): void
    {
        assertThat(
            $this->listBinding->withValue(303)
                ->getInstance($this->injector, 'int'),
            equals([303])
        );
    }

    #[Test]
    public function valueIsAddedToTypedList(): void
    {
        $value = new stdClass();
        assertThat(
            $this->listBinding->withValue($value)
                ->getInstance(
                    $this->injector,
                    new ReflectionClass(stdClass::class)
                ),
            equals([$value])
        );
    }

    #[Test]
    public function classNameIsAddedToTypedList(): void
    {
        $value = new \stdClass();
        $this->injector->returns(['getInstance' => $value]);
        assertThat(
            $this->listBinding->withValue(stdClass::class)
                ->getInstance(
                    $this->injector,
                    new ReflectionClass(stdClass::class)
                ),
            equals([$value])
        );
    }

    #[Test]
    public function invalidValueAddedToTypedListThrowsBindingException(): void
    {
        expect(function() {
            $this->listBinding->withValue(303)->getInstance(
                $this->injector,
                new ReflectionClass(stdClass::class)
            );
        })->throws(BindingException::class);
    }

    #[Test]
    public function invalidObjectAddedToTypedListThrowsBindingException(): void
    {
        expect(function() {
            $this->listBinding->withValue(new stdClass())->getInstance(
                    $this->injector,
                    new ReflectionClass(InjectionProvider::class)
            );
        })->throws(BindingException::class);
    }

    /**
     * creates mocked injection provider which returns given value
     *
     * @return  \stubbles\ioc\InjectionProvider<mixed>
     */
    private function createInjectionProvider(mixed $value): InjectionProvider
    {
        return NewInstance::of(InjectionProvider::class)
            ->returns(['get' => $value]);
    }

    #[Test]
    public function valueFromProviderIsAddedToList(): void
    {
        assertThat(
            $this->listBinding
                ->withValueFromProvider($this->createInjectionProvider(303))
                ->getInstance($this->injector, 'int'),
            equals([303])
        );
    }

    #[Test]
    public function valueFromProviderIsAddedToTypedList(): void
    {
        $value = new stdClass();
        assertThat(
            $this->listBinding
                ->withValueFromProvider($this->createInjectionProvider($value))
                ->getInstance(
                    $this->injector,
                    new ReflectionClass(stdClass::class)
                ),
            equals([$value])
        );
    }

    #[Test]
    public function invalidValueFromProviderAddedToTypedListThrowsBindingException(): void
    {
        $listBinding = $this->listBinding->withValueFromProvider(
            $this->createInjectionProvider(303)
        );
        expect(function() use ($listBinding) {
            $listBinding->getInstance(
                $this->injector,
                new ReflectionClass(stdClass::class)
            );
        })->throws(BindingException::class);
    }

    #[Test]
    public function invalidObjectFromProviderAddedToTypedListThrowsBindingException(): void
    {
        $listBinding = $this->listBinding->withValueFromProvider(
            $this->createInjectionProvider(new stdClass())
        );
        expect(function() use ($listBinding) {
            $listBinding->getInstance(
                $this->injector,
                new ReflectionClass(InjectionProvider::class)
            );
        })->throws(BindingException::class);
    }

    #[Test]
    public function valueFromProviderClassIsAddedToList(): void
    {
        $provider = $this->createInjectionProvider(303);
        $this->prepareInjector($provider);
        assertThat(
            $this->listBinding->withValueFromProvider(get_class($provider))
                ->getInstance($this->injector, 'int'),
            equals([303])
        );
    }

    #[Test]
    public function valueFromProviderClassIsAddedToTypedList(): void
    {
        $value    = new stdClass();
        $provider = $this->createInjectionProvider($value);
        $this->prepareInjector($provider);
        assertThat(
            $this->listBinding->withValueFromProvider(get_class($provider))
                ->getInstance(
                    $this->injector,
                    new ReflectionClass(stdClass::class)
                ),
            equals([$value])
        );
    }

    #[Test]
    public function invalidValueFromProviderClassAddedToTypedListThrowsBindingException(): void
    {
        $provider = $this->createInjectionProvider(303);
        $this->prepareInjector($provider);
        $listBinding = $this->listBinding->withValueFromProvider(get_class($provider));
        expect(function() use ($listBinding) {
            $listBinding->getInstance(
                $this->injector,
                new ReflectionClass(stdClass::class)
            );
        })->throws(BindingException::class);
    }

    #[Test]
    public function invalidObjectFromProviderClassAddedToTypedListThrowsBindingException(): void
    {
        $provider = $this->createInjectionProvider(new stdClass());
        $this->prepareInjector($provider);
        $listBinding = $this->listBinding->withValueFromProvider(get_class($provider));
        expect(function() use ($listBinding) {
            $listBinding->getInstance(
                $this->injector,
                new ReflectionClass(InjectionProvider::class)
            );
        })->throws(BindingException::class);
    }

    /**
     * prepares injector to return mock provider instance
     *
     * @param  InjectionProvider<stdClass>  $provider
     */
    private function prepareInjector(InjectionProvider $provider): void
    {
        $this->injector->returns(['getInstance' => $provider]);
    }

    #[Test]
    public function addInvalidProviderClassThrowsBindingException(): void
    {
        $providerClass = get_class(NewInstance::of(InjectionProvider::class));
        $this->injector->returns(['getInstance' => stdClass::class]);
        $listBinding = $this->listBinding->withValueFromProvider($providerClass);
        expect(function() use ($listBinding) {
            $listBinding->getInstance(
                $this->injector,
                new ReflectionClass(InjectionProvider::class)
            );
        })->throws(BindingException::class);
    }

    /**
     * @since  2.1.0
     */
    #[Test]
    #[Group('issue_31')]
    public function valueFromClosureIsAddedToList(): void
    {
        assertThat(
            $this->listBinding->withValueFromClosure(fn() => 303)
                ->getInstance($this->injector, 'int'),
            equals([303])
        );
    }

    /**
     * @since  2.1.0
     */
    #[Test]
    #[Group('issue_31')]
    public function valueFromClosureIsAddedToTypedList(): void
    {
        $value = new stdClass();
        assertThat(
            $this->listBinding->withValueFromClosure(fn() => $value)
                ->getInstance(
                    $this->injector,
                    new ReflectionClass(stdClass::class)
                ),
            equals([$value])
        );
    }

    /**
     * @since  2.1.0
     */
    #[Test]
    #[Group('issue_31')]
    public function invalidValueFromClosureAddedToTypedListThrowsBindingException(): void
    {
        $listBinding = $this->listBinding->withValueFromClosure(fn() => 303);
        expect(function() use ($listBinding) {
            $listBinding->getInstance(
                $this->injector,
                new ReflectionClass(stdClass::class)
            );
        })->throws(BindingException::class);
    }

    /**
     * @since  2.1.0
     */
    #[Test]
    #[Group('issue_31')]
    public function invalidObjectFromClosureAddedToTypedListThrowsBindingException(): void
    {
        $listBinding = $this->listBinding->withValueFromClosure(fn() => new stdClass());
        expect(function() use ($listBinding) {
            $listBinding->getInstance(
                $this->injector,
                new ReflectionClass(InjectionProvider::class)
            );
        })->throws(BindingException::class);
    }
}
