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
use stubbles\ioc\Injector;
use stubbles\ioc\InjectionProvider;
/**
 * Base class for multi bindings.
 *
 * @since  2.0.0
 */
abstract class MultiBinding implements Binding
{
    /**
     * name of the list or map
     *
     * @var  string
     */
    private $name;
    /**
     * created multi binding
     *
     * @var  array<string,mixed>
     */
    private $array = null;

    /**
     * constructor
     *
     * @param  string  $name  name of the list or map
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * creates a closure which returns the given value
     *
     * @param   mixed $value
     * @return  \Closure
     */
    protected function getValueCreator($value): \Closure
    {
        if (is_string($value) && class_exists($value)) {
            return function($injector) use($value) { return $injector->getInstance($value); };
        }

        return function() use($value) { return $value; };
    }

    /**
     * creates a closure which uses the given provider to create the value
     *
     * Note: class-string should actually be class-string<<InjectionProvider<mixed>>,
     * but phpstan trips up about that.
     *
     * @param   class-string|InjectionProvider<mixed>  $provider
     * @return  \Closure
     * @throws  \InvalidArgumentException
     */
    protected function getProviderCreator($provider): \Closure
    {
        if (is_string($provider)) {
            return function($injector, $name, $key) use($provider)
                   {
                       $providerInstance = $injector->getInstance($provider);
                       if (!($providerInstance instanceof InjectionProvider)) {
                           throw new BindingException('Configured provider class ' . $provider . ' for ' . $name . '[' . $key . '] is not an instance of stubbles\ioc\InjectionProvider.');
                       }

                       return $providerInstance->get();

                   };
        } elseif ($provider instanceof InjectionProvider) {
            return function() use($provider) { return $provider->get(); };
        }

        throw new \InvalidArgumentException(
                'Given provider must either be a instance of'
                . ' stubbles\ioc\InjectionProvider or a class name representing'
                . ' such a provider instance.'
        );
    }

    /**
     * returns the created instance
     *
     * @param   \stubbles\ioc\Injector                $injector
     * @param   string|\ReflectionClass<object>|null  $name
     * @return  array<mixed>
     */
    public function getInstance(Injector $injector, $name = null)
    {
        if (null === $this->array) {
            $this->array = $this->resolve($injector, $name);
        }

        return $this->array;
    }

    /**
     * creates the instance
     *
     * @param   \stubbles\ioc\Injector               $injector
     * @param   string|\ReflectionClass<object>|null $type
     * @return  array<mixed>
     * @throws  \stubbles\ioc\binding\BindingException
     */
    private function resolve(Injector $injector, $type): array
    {
        $resolved = [];
        foreach ($this->getBindings() as $key => $bindingValue) {
            $value = $bindingValue($injector, $this->name, $key);
            if ($this->isTypeMismatch($type, $value)) {
                $valueType = ((is_object($value)) ? (get_class($value)) : (gettype($value)));
                throw new BindingException('Value of type ' . $valueType . ' for ' . ((is_int($key)) ? ('list') : ('map')) . ' named ' . $this->name . ' at position ' . $key . ' is not of type ' . $type);
            }

            $resolved[$key] = $value;
        }

        return $resolved;
    }

    /**
     * checks if given type and type of value are a mismatch
     *
     * A type mismatch is defined as follows: $value is an object and it's
     * an instance of the class defined with $type. In any other case there's no
     * type mismatch
     *
     * @param   string|\ReflectionClass<object>|null  $type
     * @param   mixed                                 $value
     * @return  bool
     */
    private function isTypeMismatch($type, $value): bool
    {
        if (!($type instanceof \ReflectionClass)) {
            return false;
        }

        if (!is_object($value)) {
            return true;
        }

        return !$type->isInstance($value);
    }

    /**
     * returns list of bindings for the array to create
     *
     * @return  array<int|string,callable>
     */
    protected abstract function getBindings(): array;

    /**
     * creates a unique key for this binding
     *
     * @return  string
     */
    public function getKey(): string
    {
        return $this->getTypeKey() . '#' . $this->name;
    }

    /**
     * returns type key for for this binding
     *
     * @return  string
     */
    protected abstract function getTypeKey(): string;
}
