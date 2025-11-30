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
namespace stubbles\ioc;
use stubbles\ioc\binding\Binding;
use stubbles\ioc\binding\BindingScopes;
use stubbles\ioc\binding\ClassBinding;
use stubbles\ioc\binding\ConstantBinding;
use stubbles\ioc\binding\ListBinding;
use stubbles\ioc\binding\MapBinding;
use stubbles\ioc\binding\PropertyBinding;
use stubbles\values\Properties;
/**
 * Binder for the IoC functionality.
 *
 * @api
 */
class Binder
{
    private ?string $environment = null;
    /**
     * list of available binding scopes
     */
    private BindingScopes $scopes;
    /**
     * added bindings that are in the index not yet
     *
     * @var  Binding[]
     */
    private array $bindings = [];
    /**
     * map of list bindings
     *
     * @var  ListBinding[]
     */
    private array $listBindings = [];
    /**
     * map of map bindings
     *
     * @var  MapBinding[]
     */
    private array $mapBindings  = [];

    public function __construct(?BindingScopes $scopes = null)
    {
        $this->scopes = $scopes ?? new BindingScopes();
    }

    /**
     * adds a new binding to the injector
     */
    public function addBinding(Binding $binding): Binding
    {
         $this->bindings[] = $binding;
         return $binding;
    }

    /**
     * Bind a new interface to a class
     *
     * @template T of object
     * @param   class-string<T>  $interface
     * @return  ClassBinding<T>
     */
    public function bind(string $interface): ClassBinding
    {
        $c = new ClassBinding($interface, $this->scopes);
        $this->addBinding($c);
        return $c;
    }

    /**
     * set environment for bindings
     *
     * @since   6.0.0
     */
    public function setEnvironment(string $environment): self
    {
        $this->environment = $environment;
        return $this;
    }

    /**
     * binds properties from given properties file
     *
     * @since  4.0.0
     */
    public function bindPropertiesFromFile(string $propertiesFile, string $environment): Properties
    {
        return $this->bindProperties(
            Properties::fromFile($propertiesFile),
            $environment
        );
    }

    /**
     * binds properties
     *
     * @since  3.4.0
     */
    public function bindProperties(Properties $properties, string $environment): Properties
    {
        $this->addBinding(new PropertyBinding($properties, $environment));
        $this->bind(Properties::class)
             ->named('config.ini')
             ->toInstance($properties);
        return $properties;
    }

    /**
     * bind a constant
     */
    public function bindConstant(string $name): ConstantBinding
    {
        $b = new ConstantBinding($name);
        $this->addBinding($b);
        return $b;
    }

    /**
     * bind to a list
     *
     * If a list with given name already exists it will return exactly this list
     * to add more values to it.
     *
     * @since  2.0.0
     */
    public function bindList(string $name): ListBinding
    {
        if (!isset($this->listBindings[$name])) {
            $this->listBindings[$name] = new ListBinding($name);
            $this->addBinding($this->listBindings[$name]);
        }

        return $this->listBindings[$name];
    }

    /**
     * bind to a map
     *
     * If a map with given name already exists it will return exactly this map
     * to add more key-value pairs to it.
     *
     * @since  2.0.0
     */
    public function bindMap(string $name): MapBinding
    {
        if (!isset($this->mapBindings[$name])) {
            $this->mapBindings[$name] = new MapBinding($name);
            $this->addBinding($this->mapBindings[$name]);
        }

        return $this->mapBindings[$name];
    }

    /**
     * Get an injector for this binder
     */
    public function getInjector(): Injector
    {
        return new Injector($this->environment, $this->bindings, $this->scopes);
    }

    /**
     * creates injector instance with bindings
     *
     * @since   7.0.0
     */
    public static function createInjector(callable ...$applyBindings): Injector
    {
        $self = new self();
        foreach ($applyBindings as $applyBinding) {
            $applyBinding($self);
        }

        return $self->getInjector();
    }
}
