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
/**
 * All built-in scopes.
 *
 * @internal
 */
class BindingScopes
{
    private BindingScope $singletonScope;
    private BindingScope $sessionScope;

    /**
     * @since  1.5.0
     */
    public function  __construct(BindingScope $singletonScope = null, BindingScope $sessionScope = null)
    {
        $this->singletonScope = $singletonScope ?? new SingletonBindingScope();
        $this->sessionScope   = $sessionScope ?? new SessionBindingScope();
    }

    /**
     * returns scope for singleton objects
     *
     * @since  1.5.0
     */
    public function singleton(): BindingScope
    {
        return $this->singletonScope;
    }

    /**
     * sets the session for the session scope in case it is the built-in implementation
     *
     * @throws  \RuntimeException  in case the session scope has been replaced with another implementation
     * @since   5.4.0
     */
    public function setSession(Session $session): self
    {
        if ($this->sessionScope instanceof SessionBindingScope) {
            $this->sessionScope->setSession($session);
            return $this;
        }

        throw new \RuntimeException('Can not set session for session scope implementation ' . get_class($this->sessionScope));
    }

    /**
     * returns scope for session resources
     *
     * @since  1.5.0
     */
    public function session(): BindingScope
    {
        return $this->sessionScope;
    }
}
