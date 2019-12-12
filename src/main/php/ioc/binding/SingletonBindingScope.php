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
use stubbles\ioc\InjectionProvider;
/**
 * Ensures that an object instance is only created once.
 *
 * @internal
 * @template T of object
 */
class SingletonBindingScope implements BindingScope
{
    /**
     * instances in this scope
     *
     * @var  T[]
     */
    protected $instances = [];

    /**
     * returns the requested instance from the scope
     *
     * @param   \ReflectionClass<T>                 $impl      concrete implementation
     * @param   \stubbles\ioc\InjectionProvider<T>  $provider
     * @return  T
     */
    public function getInstance(\ReflectionClass $impl, InjectionProvider $provider)
    {
        $key = $impl->getName();
        if (!isset($this->instances[$key])) {
            $this->instances[$key] = $provider->get();
        }

        return $this->instances[$key];
    }
}
