<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\ioc\binding;
use bovigo\callmap\NewInstance;
use PHPUnit\Framework\TestCase;
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
 * @group  ioc
 * @group  ioc_binding
 */
class ListBindingTest extends TestCase
{
    /**
     * instance to test
     *
     * @type  \stubbles\ioc\binding\ListBinding
     */
    private $listBinding;
    /**
     * mocked injector
     *
     * @type  \bovigo\callmap\Proxy
     */
    private $injector;

    protected function setUp(): void
    {
        $this->injector    = NewInstance::of(Injector::class);
        $this->listBinding = new ListBinding('foo');
    }

    /**
     * @test
     */
    public function getKeyReturnsUniqueListKey()
    {
        assertThat($this->listBinding->getKey(), equals(ListBinding::TYPE . '#foo'));
    }

    /**
     * @test
     */
    public function returnsEmptyListIfNothingAdded()
    {
        assertEmptyArray($this->listBinding->getInstance($this->injector, 'int'));
    }

    /**
     * @test
     */
    public function returnsTypedEmptyListIfNothingAdded()
    {
        assertEmptyArray(
                $this->listBinding->getInstance(
                        $this->injector,
                        new \ReflectionClass(\stdClass::class)
                )
        );
    }

    /**
     * @test
     */
    public function valueIsAddedToList()
    {
        assertThat(
                $this->listBinding->withValue(303)
                        ->getInstance($this->injector, 'int'),
                equals([303])
        );
    }

    /**
     * @test
     */
    public function valueIsAddedToTypedList()
    {
        $value = new \stdClass();
        assertThat(
                $this->listBinding->withValue($value)
                        ->getInstance(
                                $this->injector,
                                new \ReflectionClass(\stdClass::class)
                        ),
                equals([$value])
        );
    }

    /**
     * @test
     */
    public function classNameIsAddedToTypedList()
    {
        $value = new \stdClass();
        $this->injector->returns(['getInstance' => $value]);
        assertThat(
                $this->listBinding->withValue(\stdClass::class)
                        ->getInstance(
                                $this->injector,
                                new \ReflectionClass(\stdClass::class)
                        ),
                equals([$value])
        );
    }

    /**
     * @test
     */
    public function invalidValueAddedToTypedListThrowsBindingException()
    {
        expect(function() {
                $this->listBinding->withValue(303)->getInstance(
                        $this->injector,
                        new \ReflectionClass(\stdClass::class)
                );
        })->throws(BindingException::class);
    }

    /**
     * @test
     */
    public function invalidObjectAddedToTypedListThrowsBindingException()
    {
        expect(function() {
            $this->listBinding->withValue(new \stdClass())->getInstance(
                    $this->injector,
                    new \ReflectionClass(InjectionProvider::class)
            );
        })->throws(BindingException::class);
    }

    /**
     * creates mocked injection provider which returns given value
     *
     * @param   mixed  $value
     * @return  \stubbles\ioc\InjectionProvider
     */
    private function createInjectionProvider($value): InjectionProvider
    {
        return NewInstance::of(InjectionProvider::class)
                ->returns(['get' => $value]);
    }

    /**
     * @test
     */
    public function valueFromProviderIsAddedToList()
    {
        assertThat(
                $this->listBinding
                        ->withValueFromProvider($this->createInjectionProvider(303))
                        ->getInstance($this->injector, 'int'),
                equals([303])
        );
    }

    /**
     * @test
     */
    public function valueFromProviderIsAddedToTypedList()
    {
        $value = new \stdClass();
        assertThat(
                $this->listBinding
                        ->withValueFromProvider($this->createInjectionProvider($value))
                        ->getInstance(
                                $this->injector,
                                new \ReflectionClass(\stdClass::class)
                        ),
                equals([$value])
        );
    }

    /**
     * @test
     */
    public function invalidValueFromProviderAddedToTypedListThrowsBindingException()
    {
        $listBinding = $this->listBinding->withValueFromProvider(
                $this->createInjectionProvider(303)
        );
        expect(function() use ($listBinding) {
                $listBinding->getInstance(
                        $this->injector,
                        new \ReflectionClass(\stdClass::class)
                );
        })->throws(BindingException::class);
    }

    /**
     * @test
     */
    public function invalidObjectFromProviderAddedToTypedListThrowsBindingException()
    {
        $listBinding = $this->listBinding->withValueFromProvider(
                $this->createInjectionProvider(new \stdClass())
        );
        expect(function() use ($listBinding) {
                $listBinding->getInstance(
                        $this->injector,
                        new \ReflectionClass(InjectionProvider::class)
                );
        })->throws(BindingException::class);
    }

    /**
     * @test
     */
    public function valueFromProviderClassIsAddedToList()
    {
        $provider = $this->createInjectionProvider(303);
        $this->prepareInjector($provider);
        assertThat(
                $this->listBinding->withValueFromProvider(get_class($provider))
                        ->getInstance($this->injector, 'int'),
                equals([303])
        );
    }

    /**
     * @test
     */
    public function valueFromProviderClassIsAddedToTypedList()
    {
        $value    = new \stdClass();
        $provider = $this->createInjectionProvider($value);
        $this->prepareInjector($provider);
        assertThat(
                $this->listBinding->withValueFromProvider(get_class($provider))
                        ->getInstance(
                                $this->injector,
                                new \ReflectionClass(\stdClass::class)
                        ),
                equals([$value])
        );
    }

    /**
     * @test
     */
    public function invalidValueFromProviderClassAddedToTypedListThrowsBindingException()
    {
        $provider = $this->createInjectionProvider(303);
        $this->prepareInjector($provider);
        $listBinding = $this->listBinding->withValueFromProvider(get_class($provider));
        expect(function() use ($listBinding) {
                $listBinding->getInstance(
                        $this->injector,
                        new \ReflectionClass(\stdClass::class)
                );
        })->throws(BindingException::class);
    }

    /**
     * @test
     */
    public function invalidObjectFromProviderClassAddedToTypedListThrowsBindingException()
    {
        $provider = $this->createInjectionProvider(new \stdClass());
        $this->prepareInjector($provider);
        $listBinding = $this->listBinding->withValueFromProvider(get_class($provider));
        expect(function() use ($listBinding) {
                $listBinding->getInstance(
                        $this->injector,
                        new \ReflectionClass(InjectionProvider::class)
                );
        })->throws(BindingException::class);
    }

    /**
     * prepares injector to return mock provider instance
     *
     * @param  InjectionProvider  $provider
     */
    private function prepareInjector(InjectionProvider $provider)
    {
        $this->injector->returns(['getInstance' => $provider]);
    }

    /**
     * @test
     */
    public function addInvalidProviderClassThrowsBindingException()
    {
        $providerClass = get_class(NewInstance::of(InjectionProvider::class));
        $this->injector->returns(['getInstance' => \stdClass::class]);
        $listBinding = $this->listBinding->withValueFromProvider($providerClass);
        expect(function() use ($listBinding) {
                $listBinding->getInstance(
                        $this->injector,
                        new \ReflectionClass(InjectionProvider::class)
                );
        })->throws(BindingException::class);
    }

    /**
     * @test
     */
    public function addInvalidProviderValueThrowsIlegalArgumentException()
    {
        expect(function() {
                $this->listBinding->withValueFromProvider(new \stdClass());
        })->throws(\InvalidArgumentException::class);
    }

    /**
     * @since  2.1.0
     * @test
     * @group  issue_31
     */
    public function valueFromClosureIsAddedToList()
    {
        assertThat(
                $this->listBinding->withValueFromClosure(function() { return 303; })
                        ->getInstance($this->injector, 'int'),
                equals([303])
        );
    }

    /**
     * @since  2.1.0
     * @test
     * @group  issue_31
     */
    public function valueFromClosureIsAddedToTypedList()
    {
        $value = new \stdClass();
        assertThat(
                $this->listBinding->withValueFromClosure(
                                function() use($value) { return $value; }
                        )->getInstance(
                                $this->injector,
                                new \ReflectionClass(\stdClass::class)
                        ),
                equals([$value])
        );
    }

    /**
     * @since  2.1.0
     * @test
     * @group  issue_31
     */
    public function invalidValueFromClosureAddedToTypedListThrowsBindingException()
    {
        $listBinding = $this->listBinding->withValueFromClosure(
                function() { return 303; }
        );
        expect(function() use ($listBinding) {
                $listBinding->getInstance(
                        $this->injector,
                        new \ReflectionClass(\stdClass::class)
                );
        })->throws(BindingException::class);
    }

    /**
     * @since  2.1.0
     * @test
     * @group  issue_31
     */
    public function invalidObjectFromClosureAddedToTypedListThrowsBindingException()
    {
        $listBinding = $this->listBinding->withValueFromClosure(
                function() { return new \stdClass(); }
        );
        expect(function() use ($listBinding) {
                $listBinding->getInstance(
                        $this->injector,
                        new \ReflectionClass(InjectionProvider::class)
                );
        })->throws(BindingException::class);
    }
}
