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
/**
 * Injection provider which uses a closure to create the instance.
 *
 * @internal
 * @since     2.1.0
 * @implements InjectionProvider<mixed>
 */
class ClosureInjectionProvider implements InjectionProvider
{
    /**
     * closure to use
     *
     * @var  \Closure
     */
    private $closure;

    /**
     * constructor
     *
     * @param  \Closure  $closure
     */
    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    /**
     * returns the value to provide
     */
    public function get(string $name = null): mixed
    {
        $closure = $this->closure;
        return $closure($name);
    }
}
