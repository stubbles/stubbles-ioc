<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\ioc;
use bovigo\callmap\NewInstance;
use PHPUnit\Framework\TestCase;
use stubbles\ioc\binding\Session;
use stubbles\test\ioc\Mikey;
use stubbles\test\ioc\Person2;

use function bovigo\assert\assertThat;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\isInstanceOf;
use function bovigo\assert\predicate\isSameAs;
/**
 * Test for stubbles\ioc\Injector with the session scope.
 *
 * @group  ioc
 */
class InjectorSessionScopeTest extends TestCase
{
    /**
     * binder instance to be used in tests
     *
     * @type  \stubbles\ioc\Injector
     */
    private $injector;

    protected function setUp(): void
    {
        $binder = new Binder();
        $binder->bind(Person2::class)
                ->to(Mikey::class)
                ->inSession();
        $this->injector = $binder->getInjector();

    }
    /**
     * @test
     * @since  5.4.0
     */
    public function canBindToSessionScopeWithoutSession()
    {
        assertTrue($this->injector->hasBinding(Person2::class));
    }

    /**
     * @test
     * @since  5.4.0
     */
    public function requestSessionScopedWithoutSessionThrowsRuntimeException()
    {
        expect(function() {
                $this->injector->getInstance(Person2::class);
        })->throws(\RuntimeException::class);
    }

    /**
     * @test
     * @since  5.4.0
     */
    public function requestSessionScopedWithSessionReturnsInstance()
    {
        $session = NewInstance::of(Session::class)
                ->returns(['hasValue' => false]);
        assertThat(
                $this->injector->setSession($session)
                        ->getInstance(Person2::class),
                isInstanceOf(Mikey::class)
        );
    }

    /**
     * @test
     * @since  5.4.0
     */
    public function setSessionAddsBindingForSession()
    {
        assertTrue(
                $this->injector->setSession(
                        NewInstance::of(Session::class),
                        Session::class
                )->hasExplicitBinding(Session::class)
        );
    }

    /**
     * @test
     * @since  5.4.0
     */
    public function setSessionAddsBindingForSessionAsSingleton()
    {
        $session = NewInstance::of(Session::class);
        assertThat(
                $this->injector->setSession(
                        $session,
                        Session::class
                )->getInstance(Session::class),
                isSameAs($session)
        );
    }
}
