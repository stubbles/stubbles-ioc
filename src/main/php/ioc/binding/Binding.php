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
     */
    public function getInstance(
        Injector $injector,
        string|ReflectionClass|null $name = null
    ): mixed;

    /**
     * creates a unique key for this binding
     */
    public function getKey(): string;
}
