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
/**
 * A binding knows how to deliver a concrete instance.
 *
 * @api
 */
interface Binding
{
    /**
     * returns the created instance
     *
     * @template T
     * @param   \stubbles\ioc\Injector  $injector
     * @param   string|\ReflectionClass|null  $name
     * @return  T
     */
    public function getInstance(Injector $injector, $name = null);

    /**
     * creates a unique key for this binding
     *
     * @return  string
     */
    public function getKey(): string;
}
