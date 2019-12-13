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
    /**
     * @var  string
     */
    private $environment;
    /**
     * list of available binding scopes
     *
     * @var  \stubbles\ioc\binding\BindingScopes
     */
    private $scopes;
    /**
     * added bindings that are in the index not yet
     *
     * @var  \stubbles\ioc\binding\Binding[]
     */
    private $bindings = [];
    /**
     * map of list bindings
     *
     * @var  \stubbles\ioc\binding\ListBinding[]
     */
    private $listBindings = [];
    /**
     * map of map bindings
     *
     * @var  \stubbles\ioc\binding\MapBinding[]
     */
    private $mapBindings  = [];

    /**
     * constructor
     *
     * @param  \stubbles\ioc\binding\BindingScopes  $scopes  optional
     */
    public function __construct(BindingScopes $scopes = null)
    {
        $this->scopes = ((null === $scopes) ? (new BindingScopes()) : ($scopes));
    }

    /**
     * adds a new binding to the injector
     *
     * @param   \stubbles\ioc\binding\Binding  $binding
     * @return  \stubbles\ioc\binding\Binding
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
     * @return  \stubbles\ioc\binding\ClassBinding<T>
     */
    public function bind($interface): ClassBinding
    {
        $c = new ClassBinding($interface, $this->scopes);
        $this->addBinding($c);
        return $c;
    }

    /**
     * set environment for bindings
     *
     * @param   string  $environment
     * @return  \stubbles\ioc\Binder
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
     * @param   string   $propertiesFile  file where properties are stored
     * @param   string   $environment     name of current environment
     * @return  \stubbles\values\Properties
     * @since   4.0.0
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
     * @param   \stubbles\values\Properties  $properties
     * @param   string                $environment  name of current environment
     * @return  \stubbles\values\Properties
     * @since   3.4.0
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
     *
     * @param   string  $name  name of constant to bind
     * @return  \stubbles\ioc\binding\ConstantBinding
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
     * @param   string  $name
     * @return  \stubbles\ioc\binding\ListBinding
     * @since   2.0.0
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
     * @param   string  $name
     * @return  \stubbles\ioc\binding\MapBinding
     * @since   2.0.0
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
     *
     * @return  \stubbles\ioc\Injector
     */
    public function getInjector(): Injector
    {
        return new Injector($this->environment, $this->bindings, $this->scopes);
    }

    /**
     * creates injector instance with bindings
     *
     * @param   callable  ...$applyBindings  optional  callables which accept instances of stubbles\ioc\Binder
     * @return  \stubbles\ioc\Injector
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
