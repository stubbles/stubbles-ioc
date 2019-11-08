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
use stubbles\ioc\Binder;
use stubbles\ioc\Injector;
use stubbles\values\Properties;
use stubbles\values\Secret;

use function bovigo\assert\assertThat;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isInstanceOf;
use function stubbles\reflect\reflect;
/**
 * Class used for tests.
 *
 * @since  4.1.3
 */
class Example
{
    public $password;
    /**
     * constructor
     *
     * @param  \stubbles\values\Secret  $password
     * @Property('example.password')
     */
    public function __construct(Secret $password)
    {
        $this->password = $password;
    }
}
/**
 * Test for stubbles\ioc\binding\PropertyBinding.
 *
 * @since  3.4.0
 * @group  ioc
 * @group  ioc_binding
 */
class PropertyBindingTest extends TestCase
{
    /**
     * mocked injector
     *
     * @type  \stubbles\ioc\Injector
     */
    private $injector;

    protected function setUp(): void
    {
        $this->injector = NewInstance::of(Injector::class);
    }

    /**
     * creates instance for given environment
     *
     * @param   string  $environment
     * @return  \stubbles\ioc\binding\PropertyBinding
     */
    private function createPropertyBinding(string $environment = 'PROD'): PropertyBinding
    {
        return new PropertyBinding(
                new Properties([
                        'PROD'   => ['foo.bar' => 'baz',
                                     'baz'     => __CLASS__ . '.class'
                                    ],
                        'config' => ['foo.bar'          => 'default',
                                     'other'            => 'someValue',
                                     'baz'              => Properties::class . '.class'
                                    ]
                ]),
                $environment

        );
    }

    /**
     * @test
     */
    public function hasValueForRuntimeMode()
    {
        assertTrue($this->createPropertyBinding()->hasProperty('foo.bar'));
    }

    /**
     * @test
     */
    public function returnsProdValueForRuntimeMode()
    {
        assertThat(
                $this->createPropertyBinding()->getInstance($this->injector, 'foo.bar'),
                equals('baz')
        );
    }

    /**
     * @test
     */
    public function hasValueForDifferentRuntimeMode()
    {
        assertTrue($this->createPropertyBinding('DEV')->hasProperty('foo.bar'));
    }

    /**
     * @test
     */
    public function returnsConfigValueForDifferentRuntimeMode()
    {
        assertThat(
                $this->createPropertyBinding('DEV')->getInstance($this->injector, 'foo.bar'),
                equals('default')
        );
    }

    /**
     * @test
     */
    public function hasValueWhenNoSpecificForRuntimeModeSet()
    {
        assertTrue($this->createPropertyBinding()->hasProperty('other'));
    }

    /**
     * @test
     */
    public function returnsConfigValueWhenNoSpecificForRuntimeModeSet()
    {
        assertThat(
                $this->createPropertyBinding()->getInstance($this->injector, 'other'),
                equals('someValue')
        );
    }

    /**
     * @test
     */
    public function doesNotHaveValueWhenPropertyNotSet()
    {
        assertFalse($this->createPropertyBinding()->hasProperty('does.not.exist'));
    }

    /**
     * @test
     */
    public function throwsBindingExceptionWhenPropertyNotSet()
    {
        $properyBinding = $this->createPropertyBinding();
        expect(function() use ($properyBinding) {
                $properyBinding->getInstance($this->injector, 'does.not.exist');
        })
        ->throws(BindingException::class)
        ->withMessage('Missing property does.not.exist for environment PROD');
    }

    /**
     * @test
     * @since  4.1.0
     */
    public function returnsParsedValuesForModeSpecificProperties()
    {
        assertThat(
                $this->createPropertyBinding()->getInstance($this->injector, 'baz'),
                equals(reflect(__CLASS__))
        );
    }

    /**
     * @test
     * @since  4.1.0
     */
    public function returnsParsedValuesForCommonProperties()
    {
        assertThat(
                $this->createPropertyBinding('DEV')->getInstance($this->injector, 'baz'),
                equals(reflect(Properties::class))
        );
    }

    /**
     * @test
     * @since  4.1.3
     */
    public function propertyBindingUsedWhenParamHasTypeHintButIsAnnotated()
    {
        try {
            $binder     = new Binder();
            $properties = new Properties(
                        ['config' => ['example.password' => 'somePassword']]
                    );
            $binder->bindProperties($properties, 'PROD');
            $example = $binder->getInjector()->getInstance(Example::class);
            assertThat($example->password, isInstanceOf(Secret::class));
        } finally {
            // ensure all references are removed to clean up environment
            unset($properties);
            $example->password = null;
            unset($example);
            unset($binder);
            gc_collect_cycles();
        }
    }
}
