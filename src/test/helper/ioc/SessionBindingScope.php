<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\test\ioc;

use ReflectionClass;
use stubbles\ioc\InjectionProvider;
use stubbles\ioc\binding\BindingScope;
/**
 * Session binding scope for the purpose of this test.
 */
class SessionBindingScope implements BindingScope
{
    /**
     * simulate session, sufficient for purpose of this test
     */
    public static array $instances = [];

    /**
     * returns the requested instance from the scope
     */
    public function getInstance(ReflectionClass $impl, InjectionProvider $provider): mixed
    {
        $key = $impl->getName();
        if (isset(self::$instances[$key])) {
            return self::$instances[$key];
        }

        self::$instances[$key] = $provider->get();
        return self::$instances[$key];
    }
}
