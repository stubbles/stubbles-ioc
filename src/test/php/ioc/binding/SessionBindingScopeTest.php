<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\ioc\binding;

use bovigo\callmap\ClassProxy;
use bovigo\callmap\NewInstance;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;
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
 */
#[Group('ioc')]
#[Group('ioc_binding')]
class SessionBindingScopeTest extends TestCase
{
    private SessionBindingScope $sessionScope;
    private Session&ClassProxy $session;
    /** @var  InjectionProvider<object>&ClassProxy */
    private InjectionProvider&ClassProxy $provider;

    protected function setUp(): void
    {
        $this->session      = NewInstance::of(Session::class);
        $this->sessionScope = new SessionBindingScope();
        $this->provider     = NewInstance::of(InjectionProvider::class);
    }

    /**
     * prepares session with given callmap
     *
     * @param  array<string,mixed>  $callmap
     */
    private function prepareSession(array $callmap): void
    {
        $this->session->returns($callmap);
        $this->sessionScope->setSession($this->session);
    }

    #[Test]
    public function returnsInstanceFromSessionIfPresent(): void
    {
        $instance = new stdClass();
        $this->prepareSession(['hasValue' => true, 'value' => $instance]);
        assertThat(
            $this->sessionScope->getInstance(
                reflect(stdClass::class),
                $this->provider
            ),
            isSameAs($instance)
        );
        verify($this->provider, 'get')->wasNeverCalled();
    }

    #[Test]
    public function createsInstanceIfNotPresent(): void
    {
        $instance = new stdClass();
        $this->prepareSession(['hasValue' => false]);
        $this->provider->returns(['get' => $instance]);
        assertThat(
            $this->sessionScope->getInstance(
                reflect(stdClass::class),
                $this->provider
            ),
            isSameAs($instance)
        );
    }

    #[Test]
    public function throwsRuntimeExceptionWhenCreatedWithoutSession(): void
    {
        expect(function() {
            $this->sessionScope->getInstance(
                reflect(stdClass::class),
                $this->provider
            );
        })->throws(\RuntimeException::class);
    }
}
