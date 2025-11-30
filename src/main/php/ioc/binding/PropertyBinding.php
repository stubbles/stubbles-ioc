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

use LogicException;
use ReflectionClass;
use stubbles\ioc\Injector;
use stubbles\values\Properties;
/**
 * Provides properties, partially based on current runtime mode.
 *
 * @since  3.4.0
 */
class PropertyBinding implements Binding
{
    /**
     * This string is used when generating the key for a constant binding.
     */
    public const string TYPE = '__PROPERTY__';

    public function __construct(private Properties $properties, private string $environment) { }

    /**
     * checks if property with given name exists
     */
    public function hasProperty(string $name): bool
    {
        if ($this->properties->containValue($this->environment, $name)) {
            return true;
        }

        return $this->properties->containValue('config', $name);
    }

    /**
     * returns the created instance
     *
     * @throws  BindingException
     */
    public function getInstance(
        Injector $injector,
        string|ReflectionClass|null $name = null
    ): mixed {
        if (null === $name) {
            throw new LogicException('$name can not be null');
        }

        if ($this->properties->containValue($this->environment, $name)) {
            return $this->properties->parseValue($this->environment, $name);
        }

        if ($this->properties->containValue('config', $name)) {
            return $this->properties->parseValue('config', $name);
        }

        throw new BindingException(
            sprintf(
                'Missing property %s for environment %s.',
                $name,
                $this->environment
            )
        );
    }

    public function getKey(): string
    {
        return self::TYPE;
    }
}
