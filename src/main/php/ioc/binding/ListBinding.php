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
use stubbles\ioc\InjectionProvider;

/**
 * Class for list bindings.
 *
 * @since  2.0.0
 */
class ListBinding extends MultiBinding
{
    /**
     * This string is used when generating the key for a list binding.
     */
    public const string TYPE = '__LIST__';
    /**
     * list of bindings for the list values
     *
     * @var  array<int,callable>
     */
    private array $bindings = [];

    /**
     * adds a value to the list
     *
     * @api
     */
    public function withValue(mixed $value): self
    {
        $this->bindings[] = $this->getValueCreator($value);
        return $this;
    }

    /**
     * adds a value to the list created by an injection provider
     *
     * @api
     */
    public function withValueFromProvider(string|InjectionProvider $provider): self
    {
        $this->bindings[] = $this->getProviderCreator($provider);
        return $this;
    }

    /**
     * adds a value which is created by given closure
     *
     * @api
     * @since   2.1.0
     */
    public function withValueFromClosure(Closure $closure): self
    {
        $this->bindings[] = $closure;
        return $this;
    }

    /**
     * returns list of bindings for the list to create
     *
     * @return  array<int,callable>
     */
    protected function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * returns type key for for this binding
     */
    protected function getTypeKey(): string
    {
        return self::TYPE;
    }
}
