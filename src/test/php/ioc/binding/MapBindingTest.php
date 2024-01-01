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
 * Test for stubbles\ioc\binding\MapBinding.
 *
 * @since  2.0.0
 */
#[Group('ioc')]
#[Group('ioc_binding')]
class MapBindingTest extends TestCase
{
    private MapBinding $mapBinding;
    private Injector&ClassProxy $injector;

    protected function setUp(): void
    {
        $this->injector   = NewInstance::of(Injector::class);
        $this->mapBinding = new MapBinding('foo');
    }

    #[Test]
    public function getKeyReturnsUniqueListKey(): void
    {
        assertThat($this->mapBinding->getKey(), equals(MapBinding::TYPE . '#foo'));
    }

    #[Test]
    public function returnsEmptyListIfNothingAdded(): void
    {
        assertEmptyArray($this->mapBinding->getInstance($this->injector, 'int'));
    }

    #[Test]
    public function returnsTypedEmptyListIfNothingAdded(): void
    {
        assertEmptyArray(
            $this->mapBinding->getInstance(
                $this->injector,
                new \ReflectionClass(\stdClass::class)
            )
        );
    }

    #[Test]
    public function valueIsAddedToList(): void
    {
        assertThat(
            $this->mapBinding->withEntry('x', 303)
                ->getInstance($this->injector, 'int'),
            equals(['x' => 303])
        );
    }

    #[Test]
    public function valueIsAddedToTypedList(): void
    {
        $value = new \stdClass();
        assertThat(
            $this->mapBinding->withEntry('x', $value)
                ->getInstance(
                    $this->injector,
                    new ReflectionClass(stdClass::class)
            ),
            equals(['x' => $value])
        );
    }

    #[Test]
    public function classNameIsAddedToTypedList(): void
    {
        $value = new stdClass();
        $this->injector->returns(['getInstance' => $value]);
        assertThat(
            $this->mapBinding->withEntry('x', stdClass::class)
                ->getInstance(
                    $this->injector,
                    new ReflectionClass(stdClass::class)
            ),
            equals(['x' => $value])
        );
    }

    #[Test]
    public function invalidValueAddedToTypedListThrowsBindingException(): void
    {
        $mapBinding = $this->mapBinding->withEntry('x', 303);
        expect(function() use ($mapBinding) {
            $mapBinding->getInstance(
                $this->injector,
                new ReflectionClass(stdClass::class)
            );
        })->throws(BindingException::class);
    }

    #[Test]
    public function invalidObjectAddedToTypedListThrowsBindingException(): void
    {
        $mapBinding = $this->mapBinding->withEntry('x', new stdClass());
        expect(function() use ($mapBinding) {
            $mapBinding->getInstance(
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
            $this->mapBinding->withEntryFromProvider(
                'x',
                $this->createInjectionProvider(303)
            )->getInstance($this->injector,'int'),
            equals(['x' => 303])
        );
    }

    #[Test]
    public function valueFromProviderIsAddedToTypedList(): void
    {
        $value = new stdClass();
        assertThat(
            $this->mapBinding->withEntryFromProvider(
                'x',
                $this->createInjectionProvider($value)
            )->getInstance(
                $this->injector,
                new ReflectionClass(stdClass::class)
            ),
            equals(['x' => $value])
        );
    }

    #[Test]
    public function invalidValueFromProviderAddedToTypedListThrowsBindingException(): void
    {
        $mapBinding = $this->mapBinding->withEntryFromProvider(
            'x',
            $this->createInjectionProvider(303)
        );
        expect(function() use ($mapBinding) {
            $mapBinding->getInstance(
                $this->injector,
                new ReflectionClass('\\stdClass')
            );
        })->throws(BindingException::class);
    }

    #[Test]
    public function invalidObjectFromProviderAddedToTypedListThrowsBindingException(): void
    {
        $mapBinding = $this->mapBinding->withEntryFromProvider(
            'x',
            $this->createInjectionProvider(new stdClass())
        );
        expect(function() use ($mapBinding) {
            $mapBinding->getInstance(
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
            $this->mapBinding->withEntryFromProvider('x', get_class($provider))
                ->getInstance($this->injector, 'int'),
            equals(['x' => 303])
        );
    }

    #[Test]
    public function valueFromProviderClassIsAddedToTypedList(): void
    {
        $value    = new \stdClass();
        $provider = $this->createInjectionProvider($value);
        $this->prepareInjector($provider);
        assertThat(
            $this->mapBinding->withEntryFromProvider('x', get_class($provider))
                ->getInstance(
                        $this->injector,
                        new ReflectionClass(stdClass::class)
                ),
            equals(['x' => $value])
        );
    }

    #[Test]
    public function invalidValueFromProviderClassAddedToTypedListThrowsBindingException(): void
    {
        $provider = $this->createInjectionProvider(303);
        $this->prepareInjector($provider);
        $mapBinding = $this->mapBinding->withEntryFromProvider('x', get_class($provider));
        expect(function() use ($mapBinding) {
            $mapBinding->getInstance(
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
        $mapBinding = $this->mapBinding->withEntryFromProvider('x', get_class($provider));
        expect(function() use ($mapBinding) {
            $mapBinding->getInstance(
                $this->injector,
                new ReflectionClass(InjectionProvider::class)
            );
        })->throws(BindingException::class);
    }

    /**
     * prepares injector to return mock provider instance
     *
     * @param  InjectionProvider<\stdClass>  $provider
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
        $mapBinding = $this->mapBinding->withEntryFromProvider('x', $providerClass);
        expect(function() use ($mapBinding) {
            $mapBinding->getInstance(
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
            $this->mapBinding->withEntryFromClosure('x', fn() => 303)
                ->getInstance($this->injector, 'int'),
            equals(['x' => 303])
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
            $this->mapBinding->withEntryFromClosure('x', fn() => $value)
                ->getInstance(
                    $this->injector,
                    new ReflectionClass(stdClass::class)
                ),
            equals(['x' => $value])
        );
    }

    /**
     * @since  2.1.0
     */
    #[Test]
    #[Group('issue_31')]
    public function invalidValueFromClosureAddedToTypedListThrowsBindingException(): void
    {
        $mapBinding = $this->mapBinding->withEntryFromClosure('x', fn() => 303);
        expect(function() use ($mapBinding) {
            $mapBinding->getInstance(
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
        $mapBinding = $this->mapBinding->withEntryFromClosure('x', fn() => new stdClass());
        expect(function() use ($mapBinding) {
            $mapBinding->getInstance(
                $this->injector,
                new ReflectionClass(InjectionProvider::class)
            );
        })->throws(BindingException::class);
    }
}
