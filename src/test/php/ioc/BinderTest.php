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
use org\bovigo\vfs\vfsStream;
use stubbles\ioc\binding\Binding;
use stubbles\ioc\binding\ClassBinding;
use stubbles\ioc\binding\ConstantBinding;
use stubbles\ioc\binding\ListBinding;
use stubbles\ioc\binding\MapBinding;
use stubbles\values\Properties;
use bovigo\callmap\NewInstance;

use function bovigo\assert\assertThat;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isInstanceOf;
use function bovigo\assert\predicate\isSameAs;
/**
 * Test for stubbles\ioc\Binder
 *
 * @group  ioc
 */
class BinderTest extends TestCase
{
    /**
     * instance to test
     *
     * @type  Binder
     */
    private $binder;

    protected function setUp(): void
    {
        $this->binder = new Binder();
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function addBindingReturnsAddedBinding()
    {
        $binding = NewInstance::of(Binding::class);
        assertThat($this->binder->addBinding($binding), isSameAs($binding));
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function bindCreatesClassBinding()
    {
        assertThat(
                $this->binder->bind('example\MyInterface'),
                isInstanceOf(ClassBinding::class)
        );
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function bindConstantCreatesBinding()
    {
        assertThat(
                $this->binder->bindConstant('foo'),
                isInstanceOf(ConstantBinding::class)
        );
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function bindListCreatesBinding()
    {
        assertThat($this->binder->bindList('foo'), isInstanceOf(ListBinding::class));
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function bindMapCreatesBinding()
    {
        assertThat($this->binder->bindMap('foo'), isInstanceOf(MapBinding::class));
    }

    /**
     * @since  2.0.0
     * @test
     */
    public function createdInjectorCanRetrieveItself()
    {
        $binder = new Binder();
        $injector = $binder->getInjector();
        assertThat($injector->getInstance(Injector::class), isSameAs($injector));
    }

    /**
     * @since  3.4.0
     * @test
     */
    public function bindPropertiesCreatesBinding()
    {
        $properties = new Properties([]);
        assertThat(
                $this->binder->bindProperties($properties, 'PROD'),
                isSameAs($properties)
        );
    }

    /**
     * @since  4.0.0
     * @test
     */
    public function bindPropertiesFromFileCreatesBinding()
    {
        $file = vfsStream::newFile('config.ini')
                ->withContent("[config]\nfoo=bar")
                ->at(vfsStream::setup());
        $properties = new Properties(['config' => ['foo' => 'bar']]);
        assertThat(
                $this->binder->bindPropertiesFromFile($file->url(), 'PROD'),
                equals($properties)
        );
    }
}
