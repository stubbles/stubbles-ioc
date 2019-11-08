<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\ioc\binding;
use bovigo\callmap\NewInstance;
use PHPUnit\Framework\TestCase;
use stubbles\ioc\InjectionProvider;

use function bovigo\assert\assertThat;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\isSameAs;
use function bovigo\callmap\verify;
use function stubbles\reflect\reflect;
/**
 * Tests for stubbles\ioc\binding\SessionBindingScope.
 *
 * @since  5.4.0
 * @group  ioc
 * @group  ioc_binding
 */
class SessionBindingScopeTest extends TestCase
{
    /**
     * instance to test
     *
     * @type  \stubbles\ioc\binding\SessionBindingScope
     */
    private $sessionScope;
    /**
     * mocked session id
     *
     * @type  \stubbles\ioc\binding\Session
     */
    private $session;
    /**
     * mocked injection provider
     *
     * @type  \stubbles\ioc\InjectionProvider
     */
    private $provider;

    protected function setUp(): void
    {
        $this->session      = NewInstance::of(Session::class);
        $this->sessionScope = new SessionBindingScope();
        $this->provider     = NewInstance::of(InjectionProvider::class);
    }

    /**
     * prepares session with given callmap
     *
     * @param  array  $callmap
     */
    private function prepareSession(array $callmap)
    {
        $this->session->returns($callmap);
        $this->sessionScope->setSession($this->session);
    }

    /**
     * @test
     */
    public function returnsInstanceFromSessionIfPresent()
    {
        $instance = new \stdClass();
        $this->prepareSession(['hasValue' => true, 'value' => $instance]);
        assertThat(
                $this->sessionScope->getInstance(
                        reflect(\stdClass::class),
                        $this->provider
                ),
                isSameAs($instance)
        );
        verify($this->provider, 'get')->wasNeverCalled();
    }

    /**
     * @test
     */
    public function createsInstanceIfNotPresent()
    {
        $instance = new \stdClass();
        $this->prepareSession(['hasValue' => false]);
        $this->provider->returns(['get' => $instance]);
        assertThat(
                $this->sessionScope->getInstance(
                        reflect(\stdClass::class),
                        $this->provider
                ),
                isSameAs($instance)
        );
    }

    /**
     * @test
     */
    public function throwsRuntimeExceptionWhenCreatedWithoutSession()
    {
        expect(function() {
                $this->sessionScope->getInstance(
                        reflect(\stdClass::class),
                        $this->provider
                );
        })->throws(\RuntimeException::class);
    }
}
