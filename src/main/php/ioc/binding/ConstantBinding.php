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
namespace stubbles\ioc\binding;

use Closure;
use ReflectionClass;
use stubbles\ioc\ClosureInjectionProvider;
use stubbles\ioc\InjectionProvider;
use stubbles\ioc\Injector;
/**
 * Binding to bind a property to a constant value.
 */
class ConstantBinding implements Binding
{
    /**
     * This string is used when generating the key for a constant binding.
     */
    public const string TYPE = '__CONSTANT__';
    private mixed $value;
    /**
     * provider to use for this binding
     *
     * @var  InjectionProvider<scalar>
     */
    private ?InjectionProvider $provider = null;
    /**
     * provider class to use for this binding (will be created via injector)
     */
    private ?string $providerClass = null;

    public function __construct(private string $name) { }

    /**
     * set the constant value
     *
     * @api
     */
    public function to(mixed $value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * set the provider that should be used to create instances for this binding
     *
     * This cannot be used in conjuction with the 'to()' or
     * 'toProviderClass()' method.
     *
     * @api
     * @param  InjectionProvider<scalar>  $provider
     * @since  1.6.0
     */
    public function toProvider(InjectionProvider $provider): self
    {
        $this->provider = $provider;
        return $this;
    }

    /**
     * set the provider class that should be used to create instances for this binding
     *
     * This cannot be used in conjuction with the 'to()' or
     * 'toProvider()' method.
     *
     * @api
     * @param   class-string<InjectionProvider<scalar>>|\ReflectionClass<InjectionProvider<scalar>>  $providerClass
     * @since   1.6.0
     */
    public function toProviderClass(string|ReflectionClass $providerClass): self
    {
        $this->providerClass = $providerClass instanceof ReflectionClass ?
            $providerClass->getName() : $providerClass;
        return $this;
    }

    /**
     * sets a closure which can create the instance
     *
     * @api
     * @since  2.1.0
     */
    public function toClosure(Closure $closure): self
    {
        $this->provider = new ClosureInjectionProvider($closure);
        return $this;
    }

    /**
     * creates a unique key for this binding
     */
    public function getKey(): string
    {
        return sprintf('%s#%s', self::TYPE, $this->name);
    }

    /**
     * returns the created instance
     *
     * @return  scalar
     */
    public function getInstance(
        Injector $injector,
        string|ReflectionClass|null $name = null
    ): mixed {
        if (null !== $this->provider) {
            return $this->provider->get($name);
        }

        if (null != $this->providerClass) {
            $provider = $injector->getInstance($this->providerClass);
            if (!($provider instanceof InjectionProvider)) {
                 throw new BindingException(
                    sprintf(
                        'Configured provider class %s for constant %s is not an instance of %.',
                        $this->providerClass,
                        $this->name,
                        InjectionProvider::class
                    )
                );
            }

            $this->provider = $provider;
            return $this->provider->get($name);
        }

        return $this->value;
    }
}
