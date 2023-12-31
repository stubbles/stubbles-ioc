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

use ReflectionClass;
use stubbles\ioc\InjectionProvider;
/**
 * Interface for all scopes.
 *
 * @api
 */
interface BindingScope
{
    /**
     * returns the requested instance from the scope
     *
     * @template T of object
     * @param   ReflectionClass<T>    $impl      concrete implementation
     * @param   InjectionProvider<T>  $provider
     * @return  T
     */
    public function getInstance(ReflectionClass $impl, InjectionProvider $provider): mixed;
}
