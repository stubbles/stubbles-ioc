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
namespace stubbles\ioc\attributes;

use Attribute;
use ReflectionClass;

/**
 * @template T of object
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class ImplementedBy
{
    /**
     * @param class-string<T> $class
     */
    public function __construct(
        private string $class,
        private ?string $environment = null
    ) { }

    public function isRestrictedByEnvironment(): bool
    {
        return null !== $this->environment;
    }

    public function matchesEnvironment(string $environment): bool
    {
        return $this->isRestrictedByEnvironment()
            && strtoupper($this->environment) === strtoupper($environment)
        ;
    }

    /**
     * @return ReflectionClass<T>
     */
    public function getClass(): ReflectionClass
    {
        return new ReflectionClass($this->class);
    }
}
