<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\ioc;
use PHPUnit\Framework\TestCase;
use stubbles\test\ioc\PropertyReceiver;
use stubbles\ioc\binding\BindingException;
use stubbles\values\Properties;

use function bovigo\assert\assertThat;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isSameAs;
use function bovigo\assert\predicate\startsWith;
/**
 * Test for property bindings.
 *
 * @group  ioc
 * @since  3.4.0
 */
class PropertyTest extends TestCase
{
    /**
     * properties to be bound
     *
     * @type  \stubbles\values\Properties
     */
    private $properties;

    protected function setUp(): void
    {
        $this->properties = new Properties(
                ['PROD'   => ['example.foo' => 'baz'],
                 'config' => ['example.foo' => 'default',
                              'example.bar' => 'someValue'
                             ]
                ]
        );
    }

    /**
     * create injector instance
     *
     * @param   string  $environment  optional
     * @return  \stubbles\ioc\Injector
     */
    private function createInjector($environment = 'PROD'): Injector
    {
        $binder = new Binder();
        $binder->bindProperties($this->properties, $environment);
        return $binder->getInjector();
    }


    /**
     * @test
     */
    public function setsCorrectPropertiesInRuntimeModeWithSpecificProperties()
    {
        $propertyReceiver = $this->createInjector('PROD')
                ->getInstance(PropertyReceiver::class);
        assertThat($propertyReceiver->foo, equals('baz'));
        assertThat($propertyReceiver->bar, equals('someValue'));
    }

    /**
     * @test
     */
    public function setsCorrectPropertiesInRuntimeModeWithDefaultProperties()
    {
        $propertyReceiver = $this->createInjector('DEV')
                ->getInstance(PropertyReceiver::class);
        assertThat($propertyReceiver->foo, equals('default'));
        assertThat($propertyReceiver->bar, equals('someValue'));
    }

    /**
     * @test
     */
    public function instanceCreationThrowsBindingExceptionWhenNoPropertiesBound()
    {
        $injector = Binder::createInjector();
        expect(function() use ($injector) {
                $injector->getInstance(PropertyReceiver::class);
        })
        ->throws(BindingException::class)
        ->message(startsWith(
                'Can not inject into ' . PropertyReceiver::class . '::__construct($foo).'
                . ' No binding for type __PROPERTY__ (named "example.foo") specified.'
        ));
    }

    /**
     * @test
     * @since  5.1.0
     */
    public function propertyInstanceIsBound()
    {
        assertThat(
                $this->createInjector()->getInstance(
                        Properties::class,
                        'config.ini'
                ),
                isSameAs($this->properties)
        );
    }
}
