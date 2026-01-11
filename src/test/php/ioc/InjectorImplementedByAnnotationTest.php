<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\ioc;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stubbles\ioc\binding\BindingException;
use stubbles\test\ioc\Mikey;
use stubbles\test\ioc\PersonAnnotated as Person;
use stubbles\test\ioc\Person3Annotated as Person3;
use stubbles\test\ioc\Person4Annotated as Person4;
use stubbles\test\ioc\Schst;

use function bovigo\assert\assertThat;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\isInstanceOf;
/**
 * Test for stubbles\ioc\Injector with the ImplementedBy annotation.
 *
 * @deprecated will be removed with 13.0.0
 */
#[Group('ioc')]
class InjectorImplementedByAnnotationTest extends TestCase
{
    #[Test]
    public function createsInstanceFromImplementedByAnnotationIfNoExplicitBindingsSet(): void
    {
        assertThat(
            Binder::createInjector()->getInstance(Person::class),
            isInstanceOf(Schst::class)
        );
    }

    #[Test]
    public function explicitBindingOverwritesImplementedByAnnotation(): void
    {
        $binder = new Binder();
        $binder->bind(Person::class)->to(Mikey::class);
        assertThat(
            $binder->getInjector()->getInstance(Person::class),
            isInstanceOf(Mikey::class)
        );
    }

    /**
     * @since  6.0.0
     */
    #[Test]
    public function fallsBackToDefaultImplementedByIfNoEnvironmentSet(): void
    {
        assertThat(
            Binder::createInjector()->getInstance(Person3::class),
            isInstanceOf(Schst::class)
        );
    }

    /**
     * @since  6.0.0
     */
    #[Test]
    public function usesFallbackIfNoSpecialImplementationDefinedForMode(): void
    {

        $binder = new Binder();
        assertThat(
            $binder->setEnvironment('PROD')
                ->getInjector()
                ->getInstance(Person3::class),
            isInstanceOf(Schst::class)
        );
    }

    /**
     * @since  6.0.0
     */
    #[Test]
    public function usesImplementationSpecifiedForMode(): void
    {

        $binder = new Binder();
        assertThat(
            $binder->setEnvironment('DEV')
                ->getInjector()
                ->getInstance(Person3::class),
            isInstanceOf(Mikey::class)
        );
    }

    /**
     * @since  6.0.0
     */
    #[Test]
    public function throwsBindingExceptionWhenNoFallbackSpecified(): void
    {
        $injector = (new Binder())
            ->setEnvironment('PROD')
            ->getInjector();
        expect(function() use ($injector) {
            $injector->getInstance(Person4::class);
        })->throws(BindingException::class);
    }

    /**
     * @since  6.0.0
     */
    #[Test]
    public function throwsBindingExceptionWhenNoFallbackSpecifiedAndNoModeSet(): void
    {
        $injector = Binder::createInjector();
        expect(function() use ($injector) {
            $injector->getInstance(Person4::class);
        })->throws(BindingException::class);
    }
}
