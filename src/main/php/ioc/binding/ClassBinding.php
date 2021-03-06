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
     * type for this binding
     *
     * @var  class-string<T>
     */
    private $type;
    /**
     * class that implements this binding
     *
     * @var  class-string<T>|\ReflectionClass<T>
     */
    private $impl;
    /**
     * Annotated with a name
     *
     * @var  string
     */
    private $name;
    /**
     * scope of the binding
     *
     * @var  \stubbles\ioc\binding\BindingScope
     */
    private $scope;
    /**
     * instance this type is bound to
     *
     * @var  T
     */
    private $instance;
    /**
     * provider to use for this binding
     *
     * @var  \stubbles\ioc\InjectionProvider<T>
     */
    private $provider;
    /**
     * provider class to use for this binding (will be created via injector)
     *
     * @var  string
     */
    private $providerClass;
    /**
     * list of available binding scopes
     *
     * @var  \stubbles\ioc\binding\BindingScopes
     */
    private $scopes;

    /**
     * constructor
     *
     * @param  class-string<T>                      $type
     * @param  \stubbles\ioc\binding\BindingScopes  $scopes
     */
    public function __construct(string $type, BindingScopes $scopes)
    {
        $this->type     = $type;
        $this->impl     = $type;
        $this->scopes   = $scopes;
    }

    /**
     * set the concrete implementation
     *
     * @api
     * @param   \ReflectionClass<T>|class-string<T>  $impl
     * @return  $this
     * @throws  \InvalidArgumentException
     */
    public function to($impl): self
    {
        if (!is_string($impl) && !($impl instanceof \ReflectionClass)) {
            throw new \InvalidArgumentException(
                    '$impl must be a string or an instance of \ReflectionClass'
            );
        }

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
     * @throws  \InvalidArgumentException
     */
    public function toInstance($instance): self
    {
        if (!($instance instanceof $this->type)) {
            throw new \InvalidArgumentException(
                    'Instance of ' . $this->type . ' expectected, '
                    . get_class($instance) . ' given.'
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
     * @param   \stubbles\ioc\InjectionProvider<T>  $provider
     * @return  $this
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
     * @param   class-string<InjectionProvider<T>>|\ReflectionClass<InjectionProvider<T>>  $providerClass
     * @return  $this
     */
    public function toProviderClass($providerClass): self
    {
        $this->providerClass = (($providerClass instanceof \ReflectionClass) ?
                                    ($providerClass->getName()) : ($providerClass));
        return $this;
    }

    /**
     * sets a closure which can create the instance
     *
     * @api
     * @param   \Closure  $closure
     * @return  $this
     * @since   2.1.0
     */
    public function toClosure(\Closure $closure): self
    {
        $this->provider = new ClosureInjectionProvider($closure);
        return $this;
    }

    /**
     * binds the class to the singleton scope
     *
     * @api
     * @return  $this
     * @since   1.5.0
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
     * @return  $this
     * @since   1.5.0
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
     * @param   \stubbles\ioc\binding\BindingScope  $scope
     * @return  $this
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
     * @param   string  $name
     * @return  $this
     */
    public function named(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * returns the created instance
     *
     * @param   \stubbles\ioc\Injector  $injector
     * @param   string                  $name
     * @return  T
     * @throws  \stubbles\ioc\binding\BindingException
     */
    public function getInstance(Injector $injector, $name = null)
    {
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
     * @return  \ReflectionClass<T>
     */
    private function impl(): \ReflectionClass
    {
      if (is_string($this->impl)) {
          $this->impl = new \ReflectionClass($this->impl);
      }

      return $this->impl;
    }

    /**
     * @param   Injector  $injector
     * @return  InjectionProvider<T>
     * @throws  BindingException
     */
    private function createInjectionProvider(Injector $injector): InjectionProvider
    {
      if (null != $this->providerClass) {
          $provider = $injector->getInstance($this->providerClass);
          if (!($provider instanceof InjectionProvider)) {
              throw new BindingException(
                  'Configured provider class '
                  . $this->providerClass . ' for type ' . $this->type
                  . ' is not an instance of ' . InjectionProvider::class . '.');
          }

          return $provider;
      }

      /** @var InjectionProvider<T> $default */
      $default = new DefaultInjectionProvider($injector, $this->impl());
      return $default;
    }

    /**
     * creates a unique key for this binding
     *
     * @return  string
     */
    public function getKey(): string
    {
        if (null === $this->name) {
            return $this->type;
        }

        return $this->type . '#' . $this->name;
    }
}
