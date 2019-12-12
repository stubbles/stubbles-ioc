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
 *
 * @group  ioc
 */
class InjectorConstantTest extends TestCase
{
    /**
     * combined helper assertion for the test
     *
     * @param  Injector  $injector
     */
    private function assertConstantInjection(Injector $injector): void
    {
        $question = $injector->getInstance(Question::class);
        assertThat($question, equals(new Question(42)));
    }

    /**
     * @test
     */
    public function injectConstant(): void
    {
        $binder = new Binder();
        $binder->bindConstant('answer')->to(42);
        $this->assertConstantInjection($binder->getInjector());
    }

    /**
     * @test
     */
    public function checkForNonExistingConstantReturnsFalse(): void
    {
        assertFalse(Binder::createInjector()->hasConstant('answer'));
    }

    /**
     * @test
     */
    public function retrieveNonExistingConstantThrowsBindingException(): void
    {
        expect(function() {
                Binder::createInjector()->getConstant('answer');
        })->throws(BindingException::class);
    }

    /**
     * @test
     * @since  1.1.0
     */
    public function checkForExistingConstantReturnsTrue(): void
    {
        $binder = new Binder();
        $binder->bindConstant('answer')->to(42);
        assertTrue($binder->getInjector()->hasConstant('answer'));
    }

    /**
     * @test
     * @since  1.1.0
     */
    public function retrieveExistingConstantReturnsValue(): void
    {
        $binder = new Binder();
        $binder->bindConstant('answer')->to(42);
        assertThat($binder->getInjector()->getConstant('answer'), equals(42));
    }

    /**
     * @test
     * @group  ioc_constantprovider
     * @since  1.6.0
     */
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
     * @test
     * @group  ioc_constantprovider
     * @since  1.6.0
     */
    public function constantViaInvalidInjectionProviderClassThrowsBindingException(): void
    {
        $binder = new Binder();
        $binder->bindConstant('answer')
               ->toProviderClass(\stdClass::class);
        expect(function() use ($binder) {
                $binder->getInjector()->getConstant('answer');
        })->throws(BindingException::class);
    }

    /**
     * @test
     * @group  ioc_constantprovider
     * @since  1.6.0
     */
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
     * @test
     * @group  ioc_constantprovider
     * @since  1.6.0
     */
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
     * @test
     * @group  issue_31
     */
    public function injectConstantViaClosure(): void
    {
        $binder = new Binder();
        $binder->bindConstant('answer')->toClosure(function() { return 42; });
        $this->assertConstantInjection($binder->getInjector());
    }
}
