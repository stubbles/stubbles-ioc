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
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
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
 */
#[Group('ioc')]
class InjectorSessionScopeTest extends TestCase
{
    private Injector $injector;

    protected function setUp(): void
    {
        $binder = new Binder();
        $binder->bind(Person2::class)
            ->to(Mikey::class)
            ->inSession();
        $this->injector = $binder->getInjector();

    }

    /**
     * @since  5.4.0
     */
    #[Test]
    public function canBindToSessionScopeWithoutSession(): void
    {
        assertTrue($this->injector->hasBinding(Person2::class));
    }

    /**
     * @since  5.4.0
     */
    #[Test]
    public function requestSessionScopedWithoutSessionThrowsRuntimeException(): void
    {
        expect(function() {
            $this->injector->getInstance(Person2::class);
        })->throws(RuntimeException::class);
    }

    /**
     * @since  5.4.0
     */
    #[Test]
    public function requestSessionScopedWithSessionReturnsInstance(): void
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
     * @since  5.4.0
     */
    #[Test]
    public function setSessionAddsBindingForSession(): void
    {
        assertTrue(
            $this->injector->setSession(
                NewInstance::of(Session::class),
                Session::class
            )->hasExplicitBinding(Session::class)
        );
    }

    /**
     * @since  5.4.0
     */
    #[Test]
    public function setSessionAddsBindingForSessionAsSingleton(): void
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
