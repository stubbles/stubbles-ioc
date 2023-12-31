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
 * Class for map bindings.
 *
 * @since  2.0.0
 */
class MapBinding extends MultiBinding
{
    /**
     * This string is used when generating the key for a map binding.
     */
    public const TYPE = '__MAP__';
    /**
     * list of bindings for the map values
     *
     * @var  array<string,callable>
     */
    private array $bindings = [];

    /**
     * adds an entry to the list
     *
     * @api
     */
    public function withEntry(string $key, mixed $value): self
    {
        $this->bindings[$key] = $this->getValueCreator($value);
        return $this;
    }

    /**
     * adds an entry to the map created by an injection provider
     *
     * Note: class-string should actually be class-string<<InjectionProvider<mixed>>,
     * but phpstan trips up about that.
     *
     * @api
     */
    public function withEntryFromProvider(string $key, string|InjectionProvider $provider): self
    {
        $this->bindings[$key] = $this->getProviderCreator($provider);
        return $this;
    }

    /**
     * adds an entry which is created by given closure
     *
     * @api
     * @since   2.1.0
     */
    public function withEntryFromClosure(string $key, Closure $closure): self
    {
        $this->bindings[$key] = $closure;
        return $this;
    }

    /**
     * returns list of bindings for the map to create
     *
     * @return  array<string,callable>
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
