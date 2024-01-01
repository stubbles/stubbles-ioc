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
use stubbles\ioc\binding\BindingException;
use stubbles\test\ioc\AnswerConstantProvider;
use stubbles\test\ioc\Question;

use function bovigo\assert\assertThat;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
/**
 * Test for stubbles\ioc\Injector with constant binding.
 */
#[Group('ioc')]
class InjectorConstantTest extends TestCase
{
    /**
     * combined helper assertion for the test
     */
    private function assertConstantInjection(Injector $injector): void
    {
        $question = $injector->getInstance(Question::class);
        assertThat($question, equals(new Question(42)));
    }

    #[Test]
    public function injectConstant(): void
    {
        $binder = new Binder();
        $binder->bindConstant('answer')->to(42);
        $this->assertConstantInjection($binder->getInjector());
    }

    #[Test]
    public function checkForNonExistingConstantReturnsFalse(): void
    {
        assertFalse(Binder::createInjector()->hasConstant('answer'));
    }

    #[Test]
    public function retrieveNonExistingConstantThrowsBindingException(): void
    {
        expect(fn() => Binder::createInjector()->getConstant('answer'))
            ->throws(BindingException::class);
    }

    /**
     * @since  1.1.0
     */
    #[Test]
    public function checkForExistingConstantReturnsTrue(): void
    {
        $binder = new Binder();
        $binder->bindConstant('answer')->to(42);
        assertTrue($binder->getInjector()->hasConstant('answer'));
    }

    /**
     * @since  1.1.0
     */
    #[Test]
    public function retrieveExistingConstantReturnsValue(): void
    {
        $binder = new Binder();
        $binder->bindConstant('answer')->to(42);
        assertThat($binder->getInjector()->getConstant('answer'), equals(42));
    }

    /**
     * @since  1.6.0
     */
    #[Test]
    #[Group('ioc_constantprovider')]
    public function constantViaInjectionProviderInstance(): void
    {
        $binder = new Binder();
        $binder->bindConstant('answer')
            ->toProvider(
                NewInstance::of(InjectionProvider::class)
                    ->returns(['get' => 42])
            );
        $injector = $binder->getInjector();
        assertTrue($injector->hasConstant('answer'));
        assertThat($injector->getConstant('answer'), equals(42));
        $this->assertConstantInjection($binder->getInjector());
    }

    /**
     * @since  1.6.0
     */
    #[Test]
    #[Group('ioc_constantprovider')]
    public function constantViaInjectionProviderClass(): void
    {
        $binder = new Binder();
        $binder->bindConstant('answer')
            ->toProviderClass(
                new \ReflectionClass(AnswerConstantProvider::class)
            );
        $injector = $binder->getInjector();
        assertTrue($injector->hasConstant('answer'));
        assertThat($injector->getConstant('answer'), equals(42));
        $this->assertConstantInjection($binder->getInjector());
    }

    /**
     * @since  1.6.0
     */
    #[Test]
    #[Group('ioc_constantprovider')]
    public function constantViaInjectionProviderClassName(): void
    {
        $binder = new Binder();
        $binder->bindConstant('answer')
            ->toProviderClass(AnswerConstantProvider::class);
        $injector = $binder->getInjector();
        assertTrue($injector->hasConstant('answer'));
        assertThat($injector->getConstant('answer'), equals(42));
        $this->assertConstantInjection($binder->getInjector());
    }

    /**
     * @since  2.1.0
     */
    #[Test]
    #[Group('issue_31')]
    public function injectConstantViaClosure(): void
    {
        $binder = new Binder();
        $binder->bindConstant('answer')->toClosure(fn() => 42);
        $this->assertConstantInjection($binder->getInjector());
    }
}
