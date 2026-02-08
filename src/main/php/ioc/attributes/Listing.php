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

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER)]
class Listing
{
    public function __construct(private string $name) { }

    public function getName(): string|ReflectionClass
    {
        if (interface_exists($this->name) || class_exists($this->name)) {
            return new ReflectionClass($this->name);
        }

        return $this->name;
    }
}
