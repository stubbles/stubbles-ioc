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
     * created multi binding
     *
     * @var  array<string,mixed>
     */
    private ?array $array = null;


    public function __construct(private string $name) { }

    /**
     * creates a callable which returns the given value
     */
    protected function getValueCreator(mixed $value): callable
    {
        if (is_string($value) && class_exists($value)) {
            return fn($injector) => $injector->getInstance($value);
        }

        return fn() => $value;
    }

    /**
     * creates a callable which uses the given provider to create the value
     */
    protected function getProviderCreator(string|InjectionProvider $provider): callable
    {
        if (is_string($provider)) {
            return function(Injector $injector, string $name, string|int $key) use($provider)
            {
                $providerInstance = $injector->getInstance($provider);
                if (!($providerInstance instanceof InjectionProvider)) {
                    throw new BindingException(
                        sprintf(
                            'Configured provider class %s for %s[%s] is not an instance of %s.',
                            $provider,
                            $name,
                            (string) $key,
                            InjectionProvider::class
                        )
                    );
                }

                return $providerInstance->get();

            };
        }

        return fn() => $provider->get();
    }

    /**
     * returns the created instance
     *
     * @return  array<mixed>
     */
    public function getInstance(
        Injector $injector,
        string|ReflectionClass|null $name = null
    ): mixed {
        if (null === $this->array) {
            $this->array = $this->resolve($injector, $name);
        }

        return $this->array;
    }

    /**
     * creates the instance
     *
     * @return  array<mixed>
     * @throws  BindingException
     */
    private function resolve(
        Injector $injector,
        string|ReflectionClass|null $type
    ): array {
        $resolved = [];
        foreach ($this->getBindings() as $key => $bindingValue) {
            $value = $bindingValue($injector, $this->name, $key);
            if ($this->isTypeMismatch($type, $value)) {
                $valueType = ((is_object($value)) ? (get_class($value)) : (gettype($value)));
                throw new BindingException(
                    sprintf(
                        'Value of type %s for %s named %s at position %s is not of type %s.',
                        $valueType,
                        ((is_int($key)) ? ('list') : ('map')),
                        $this->name,
                        $key,
                        $type instanceof ReflectionClass ? $type->getName() : $type
                    )
                );
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
     */
    private function isTypeMismatch(
        string|ReflectionClass|null $type,
        mixed $value
    ): bool {
        if (!($type instanceof ReflectionClass)) {
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
    abstract protected function getBindings(): array;

    /**
     * creates a unique key for this binding
     */
    public function getKey(): string
    {
        return sprintf('%s#%s', $this->getTypeKey(), $this->name);
    }

    /**
     * returns type key for for this binding
     */
    abstract protected function getTypeKey(): string;
}
