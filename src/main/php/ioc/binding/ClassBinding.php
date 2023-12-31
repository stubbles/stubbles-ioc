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
use InvalidArgumentException;
use ReflectionClass;
use stubbles\ioc\ClosureInjectionProvider;
use stubbles\ioc\DefaultInjectionProvider;
use stubbles\ioc\InjectionProvider;
use stubbles\ioc\Injector;

use function stubbles\reflect\annotationsOf;
/**
 * Binding to bind an interface to an implementation.
 *
 * Please note that you can do a binding to a class or to an instance, or to an
 * injection provider, or to an injection provider class. These options are
 * mutually exclusive and have a predictive order:
 * 1. Instance
 * 2. Provider instance
 * 3. Provider class
 * 4. Concrete implementation class
 *
 * @template T of object
 */
class ClassBinding implements Binding
{
    /**
     * class that implements this binding
     *
     * @var  class-string<T>|\ReflectionClass<T>
     */
    private string|ReflectionClass $impl;
    private ?string $name = null;
    private ?BindingScope $scope = null;
    /**
     * instance this type is bound to
     *
     * @var  T
     */
    private ?object $instance = null;
    /**
     * provider to use for this binding
     *
     * @var  \stubbles\ioc\InjectionProvider<T>
     */
    private ?InjectionProvider $provider = null;
    /**
     * provider class to use for this binding (will be created via injector)
     */
    private ?string $providerClass = null;

    /**
     * constructor
     *
     * @param  class-string<T>  $type
     */
    public function __construct(private string $type, private BindingScopes $scopes)
    {
        $this->impl = $type;
    }

    /**
     * set the concrete implementation
     *
     * @api
     * @param  ReflectionClass<T>|class-string<T>  $impl
     */
    public function to(string|ReflectionClass $impl): self
    {
        $this->impl = $impl;
        return $this;
    }

    /**
     * set the concrete instance
     *
     * This cannot be used in conjuction with the 'toProvider()' or
     * 'toProviderClass()' method.
     *
     * @api
     * @param   T  $instance
     * @return  $this
     * @throws  InvalidArgumentException
     */
    public function toInstance(object $instance): self
    {
        if (!($instance instanceof $this->type)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Instance of %s expectected, %s given',
                    $this->type,
                    get_class($instance)
                )
            );
        }

        $this->instance = $instance;
        return $this;
    }

    /**
     * set the provider that should be used to create instances for this binding
     *
     * This cannot be used in conjuction with the 'toInstance()' or
     * 'toProviderClass()' method.
     *
     * @api
     * @param  InjectionProvider<T>  $provider
     */
    public function toProvider(InjectionProvider $provider): self
    {
        $this->provider = $provider;
        return $this;
    }

    /**
     * set the provider class that should be used to create instances for this binding
     *
     * This cannot be used in conjuction with the 'toInstance()' or
     * 'toProvider()' method.
     *
     * @api
     * @param   class-string<InjectionProvider<T>>|ReflectionClass<InjectionProvider<T>>  $providerClass
     * @return  $this
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
     * binds the class to the singleton scope
     *
     * @api
     * @since  1.5.0
     */
    public function asSingleton(): self
    {
        $this->scope = $this->scopes->singleton();
        return $this;
    }

    /**
     * binds the class to the session scope
     *
     * @api
     * @since  1.5.0
     */
    public function inSession(): self
    {
        $this->scope = $this->scopes->session();
        return $this;
    }

    /**
     * set the scope
     *
     * @api
     */
    public function in(BindingScope $scope): self
    {
        $this->scope = $scope;
        return $this;
    }

    /**
     * Set the name of the injection
     *
     * @api
     */
    public function named(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * returns the created instance
     *
     * @return  T
     * @throws  BindingException
     */
    public function getInstance(
        Injector $injector,
        string|ReflectionClass|null $name = null
    ): mixed {
        if (null !== $this->instance) {
            return $this->instance;
        }

        if (null === $this->scope && annotationsOf($this->impl())->contain('Singleton')) {
            $this->scope = $this->scopes->singleton();
        }

        if (null === $this->provider) {
            $this->provider = $this->createInjectionProvider($injector);
        }

        if (null !== $this->scope) {
            return $this->scope->getInstance($this->impl(), $this->provider);
        }

        return $this->provider->get($name);
    }

    /**
     * @return  ReflectionClass<T>
     */
    private function impl(): ReflectionClass
    {
      if (is_string($this->impl)) {
          $this->impl = new ReflectionClass($this->impl);
      }

      return $this->impl;
    }

    /**
     * @return  InjectionProvider<T>
     * @throws  BindingException
     */
    private function createInjectionProvider(Injector $injector): InjectionProvider
    {
        if (null != $this->providerClass) {
            $provider = $injector->getInstance($this->providerClass);
            if (!($provider instanceof InjectionProvider)) {
                throw new BindingException(
                    sprintf(
                        'Configured provider class %s for type %s is not an instance of %s.',
                        $this->providerClass,
                        $this->type,
                        InjectionProvider::class
                    )
                );
            }

            return $provider;
        }

        return new DefaultInjectionProvider($injector, $this->impl());
    }

    /**
     * creates a unique key for this binding
     */
    public function getKey(): string
    {
        if (null === $this->name) {
            return $this->type;
        }

        return sprintf('%s#%s', $this->type, $this->name);
    }
}
