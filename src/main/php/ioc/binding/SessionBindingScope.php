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
use RuntimeException;
use stubbles\ioc\InjectionProvider;
use stubbles\ioc\binding\BindingScope;
/**
 * Interface for session storages.
 *
 * @since  5.4.0
 */
class SessionBindingScope implements BindingScope
{
    private const SESSION_KEY  = 'stubbles.ioc.session.scope#';
    private ?Session $session = null;

    /**
     * sets actual session
     */
    public function setSession(Session $session): self
    {
        $this->session = $session;
        return $this;
    }

    /**
     * returns the requested instance from the scope
     *
     * @template T of object
     * @param   ReflectionClass<T>    $impl      concrete implementation
     * @param   InjectionProvider<T>  $provider
     * @return  T
     * @throws  RuntimeException
     */
    public function getInstance(ReflectionClass $impl, InjectionProvider $provider): mixed
    {
        if (null === $this->session) {
            throw new RuntimeException(
                sprintf(
                    'Can not create session-scoped instance for %s, no session set in session scope.',
                    $impl->getName()
                )
            );
        }

        $key = self::SESSION_KEY . $impl->getName();
        if ($this->session->hasValue($key)) {
            return $this->session->value($key);
        }

        $instance = $provider->get();
        $this->session->putValue($key, $instance);
        return $instance;
    }
}
