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

#[Attribute(Attribute::TARGET_CLASS)]
/**
 * @template T of object
 */
class ProvidedBy
{
    /**
     * @param class-string<T> $providerClass
     */
    public function __construct(private string $providerClass) { }

    /**
     * @return ReflectionClass<T>
     */
    public function getProviderClass(): ReflectionClass
    {
        return new ReflectionClass($this->providerClass);
    }
}
