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
use stubbles\ioc\Binder;
use stubbles\ioc\Injector;
use stubbles\values\Properties;
use stubbles\values\Secret;

use function bovigo\assert\assertThat;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\fail;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isInstanceOf;
use function stubbles\reflect\reflect;
/**
 * Test for stubbles\ioc\binding\PropertyBinding.
 *
 * @since  3.4.0
 */
#[Group('ioc')]
#[Group('ioc_binding')]
class PropertyBindingTest extends TestCase
{
    private Injector&ClassProxy $injector;

    protected function setUp(): void
    {
        $this->injector = NewInstance::of(Injector::class);
    }

    /**
     * creates instance for given environment
     */
    private function createPropertyBinding(string $environment = 'PROD'): PropertyBinding
    {
        return new PropertyBinding(
            new Properties([
                'PROD'   => [
                    'foo.bar' => 'baz',
                    'baz'     => __CLASS__ . '.class'
                ],
                'config' => [
                    'foo.bar' => 'default',
                    'other'   => 'someValue',
                    'baz'     => Properties::class . '.class'
                ]
            ]),
            $environment

        );
    }

    #[Test]
    public function hasValueForRuntimeMode(): void
    {
        assertTrue($this->createPropertyBinding()->hasProperty('foo.bar'));
    }

    #[Test]
    public function returnsProdValueForRuntimeMode(): void
    {
        assertThat(
            $this->createPropertyBinding()->getInstance($this->injector, 'foo.bar'),
            equals('baz')
        );
    }

    #[Test]
    public function hasValueForDifferentRuntimeMode(): void
    {
        assertTrue($this->createPropertyBinding('DEV')->hasProperty('foo.bar'));
    }

    #[Test]
    public function returnsConfigValueForDifferentRuntimeMode(): void
    {
        assertThat(
            $this->createPropertyBinding('DEV')->getInstance($this->injector, 'foo.bar'),
            equals('default')
        );
    }

    #[Test]
    public function hasValueWhenNoSpecificForRuntimeModeSet(): void
    {
        assertTrue($this->createPropertyBinding()->hasProperty('other'));
    }

    #[Test]
    public function returnsConfigValueWhenNoSpecificForRuntimeModeSet(): void
    {
        assertThat(
            $this->createPropertyBinding()->getInstance($this->injector, 'other'),
            equals('someValue')
        );
    }

    #[Test]
    public function doesNotHaveValueWhenPropertyNotSet(): void
    {
        assertFalse($this->createPropertyBinding()->hasProperty('does.not.exist'));
    }

    #[Test]
    public function throwsBindingExceptionWhenPropertyNotSet(): void
    {
        $properyBinding = $this->createPropertyBinding();
        expect(function() use ($properyBinding) {
            $properyBinding->getInstance($this->injector, 'does.not.exist');
        })
            ->throws(BindingException::class)
            ->withMessage('Missing property does.not.exist for environment PROD.');
    }

    /**
     * @since  4.1.0
     */
    #[Test]
    public function returnsParsedValuesForModeSpecificProperties(): void
    {
        assertThat(
            $this->createPropertyBinding()->getInstance($this->injector, 'baz'),
            equals(reflect(__CLASS__))
        );
    }

   /**
     * @since  4.1.0
     */
    #[Test]
    public function returnsParsedValuesForCommonProperties(): void
    {
        assertThat(
            $this->createPropertyBinding('DEV')->getInstance($this->injector, 'baz'),
            equals(reflect(Properties::class))
        );
    }

    /**
     * @since  4.1.3
     */
    #[Test]
    public function propertyBindingUsedWhenParamHasTypeHintButIsAnnotated(): void
    {
        $binder     = new Binder();
        $properties = new Properties([
            'config' => ['example.password' => 'somePassword']
        ]);
        $binder->bindProperties($properties, 'PROD');
        $injector = $binder->getInjector();
        try {
            $example = $injector->getInstance(Example::class);
            assertThat($example->password, isInstanceOf(Secret::class));
        } catch (\Throwable $e) {
            fail($e->getMessage());
        } finally {
            // ensure all references are removed to clean up environment
            unset($properties);
            if (isset($example)) {
                $example->password = null;
                unset($example);
            }

            unset($binder);
            gc_collect_cycles();
        }
    }
}
