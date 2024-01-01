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
 * @since  3.4.0
 */
#[Group('ioc')]
class PropertyTest extends TestCase
{
    private Properties $properties;

    protected function setUp(): void
    {
        $this->properties = new Properties([
            'PROD'   => ['example.foo' => 'baz'],
            'config' => [
                'example.foo' => 'default',
                'example.bar' => 'someValue'
            ]
        ]);
    }

    private function createInjector(string $environment = 'PROD'): Injector
    {
        $binder = new Binder();
        $binder->bindProperties($this->properties, $environment);
        return $binder->getInjector();
    }


    #[Test]
    public function setsCorrectPropertiesInRuntimeModeWithSpecificProperties(): void
    {
        $propertyReceiver = $this->createInjector('PROD')
            ->getInstance(PropertyReceiver::class);
        assertThat($propertyReceiver->foo, equals('baz'));
        assertThat($propertyReceiver->bar, equals('someValue'));
    }

    #[Test]
    public function setsCorrectPropertiesInRuntimeModeWithDefaultProperties(): void
    {
        $propertyReceiver = $this->createInjector('DEV')
            ->getInstance(PropertyReceiver::class);
        assertThat($propertyReceiver->foo, equals('default'));
        assertThat($propertyReceiver->bar, equals('someValue'));
    }

    #[Test]
    public function instanceCreationThrowsBindingExceptionWhenNoPropertiesBound(): void
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
     * @since  5.1.0
     */
    #[Test]
    public function propertyInstanceIsBound(): void
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
