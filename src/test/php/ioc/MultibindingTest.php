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
use stubbles\ioc\binding\ListBinding;
use stubbles\ioc\binding\MapBinding;
use stubbles\test\ioc\Plugin;
use stubbles\test\ioc\PluginHandler;

use function bovigo\assert\assertThat;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
/**
 * Test for list and map bindings.
 *
 * @group  ioc
 */
class MultibindingTest extends TestCase
{
    /**
     * @test
     */
    public function createsList(): void
    {
        $binder = new Binder();
        $binder->bindList('listConfig')
                ->withValue(303)
                ->withValueFromProvider($this->createProviderForValue(313))
                ->withValueFromClosure(function() { return 323; });
        $binder->bindMap('mapConfig');
        $pluginHandler = $this->createPluginHandler($binder);
        assertThat($pluginHandler->getConfigList(), equals([303, 313, 323]));
    }

    /**
     * @test
     */
    public function injectorReturnsFalseForNonAddedListOnCheck(): void
    {
        assertFalse(
                Binder::createInjector()
                        ->hasBinding(ListBinding::TYPE, 'listConfig')
        );
    }

    /**
     * @test
     */
    public function injectorReturnsTrueForAddedListOnCheck(): void
    {
        $binder = new Binder();
        $binder->bindList('listConfig')
                ->withValue(303)
                ->withValueFromProvider($this->createProviderForValue(313))
                ->withValueFromClosure(function() { return 323; });
        assertTrue(
                $binder->getInjector()
                        ->hasBinding(ListBinding::TYPE, 'listConfig')
        );
    }

    /**
     * @test
     */
    public function injectorRetrievesNonAddedListThrowsBindingException(): void
    {
        $injector = Binder::createInjector();
        expect(function() use ($injector) {
                $injector->getInstance(ListBinding::TYPE, 'listConfig');
        })->throws(BindingException::class);
    }

    /**
     * @test
     */
    public function injectorRetrievesAddedList(): void
    {
        $binder = new Binder();
        $binder->bindList('listConfig')
                ->withValue(303)
                ->withValueFromProvider($this->createProviderForValue(313))
                ->withValueFromClosure(function() { return 323; });
        assertThat(
                $binder->getInjector()->getInstance(ListBinding::TYPE, 'listConfig'),
                equals([303, 313, 323])
        );
    }

    /**
     * @test
     */
    public function bindListMoreThanOnceAddsToSameList(): void
    {
        $binder = new Binder();
        $binder->bindList('listConfig')
                ->withValue(303);
        $binder->bindList('listConfig')
                ->withValueFromProvider($this->createProviderForValue(313));
        $binder->bindList('listConfig')
                ->withValueFromClosure(function() { return 323; });
        $binder->bindMap('mapConfig');
        $pluginHandler = $this->createPluginHandler($binder);
        assertThat(
                $pluginHandler->getConfigList(),
                equals([303, 313, 323])
        );
    }

    /**
     * @test
     */
    public function injectorReturnsFalseForNonAddedMapOnCheck(): void
    {
        assertFalse(
                Binder::createInjector()->hasBinding(MapBinding::TYPE, 'mapConfig')
        );
    }

    /**
     * @test
     */
    public function injectorReturnsTrueForAddedMapOnCheck(): void
    {
        $binder = new Binder();
        $binder->bindMap('mapConfig')
                ->withEntry('tb', 303)
                ->withEntryFromProvider('dd', $this->createProviderForValue(313))
                ->withEntryFromClosure('hf', function() { return 323; });
        assertTrue(
                $binder->getInjector()->hasBinding(MapBinding::TYPE, 'mapConfig')
        );
    }

    /**
     * @test
     */
    public function injectorRetrievesNonAddedMapThrowsBindingException(): void
    {
        $injector = Binder::createInjector();
        expect(function() use ($injector) {
                $injector->getInstance(MapBinding::TYPE, 'mapConfig');
        })->throws(BindingException::class);
    }

    /**
     * @test
     */
    public function injectorRetrievesAddedMap(): void
    {
        $binder = new Binder();
        $binder->bindMap('mapConfig')
                ->withEntry('tb', 303)
                ->withEntryFromProvider('dd', $this->createProviderForValue(313))
                ->withEntryFromClosure('hf', function() { return 323; });
        assertThat(
                $binder->getInjector()->getInstance(MapBinding::TYPE, 'mapConfig'),
                equals(['tb' => 303, 'dd' => 313, 'hf' => 323])
        );
    }

    /**
     * @test
     */
    public function createsMap(): void
    {
        $binder = new Binder();
        $binder->bindList('listConfig');
        $binder->bindMap('mapConfig')
                ->withEntry('tb', 303)
                ->withEntryFromProvider('dd', $this->createProviderForValue(313))
                ->withEntryFromClosure('hf', function() { return 323; });
        $pluginHandler = $this->createPluginHandler($binder);
        assertThat(
                $pluginHandler->getConfigMap(),
                equals(['tb' => 303, 'dd' => 313, 'hf' => 323])
        );
    }

    /**
     * @test
     */
    public function bindMapMoreThanOnceAddsToSameMap(): void
    {
        $binder = new Binder();
        $binder->bindList('listConfig');
        $binder->bindMap('mapConfig')
                ->withEntry('tb', 303);
        $binder->bindMap('mapConfig')
                ->withEntryFromProvider('dd', $this->createProviderForValue(313));
        $binder->bindMap('mapConfig')
                ->withEntryFromClosure('hf', function() { return 323; });
        $pluginHandler = $this->createPluginHandler($binder);
        assertThat(
                $pluginHandler->getConfigMap(),
                equals(['tb' => 303, 'dd' => 313, 'hf' => 323])
        );
    }

    /**
     * @test
     */
    public function createTypedList(): void
    {
        $plugin1 = NewInstance::of(Plugin::class);
        $plugin2 = NewInstance::of(Plugin::class);
        $plugin3 = NewInstance::of(Plugin::class);
        $binder  = new Binder();
        $binder->bindList('listConfig');
        $binder->bindMap('mapConfig');
        $binder->bindList(Plugin::class)
                ->withValue($plugin1)
                ->withValueFromProvider($this->createProviderForValue($plugin2))
                ->withValueFromClosure(
                        function() use($plugin3) { return $plugin3; }
                );
        $pluginHandler = $this->createPluginHandler($binder);
        assertThat(
                $pluginHandler->getPluginList(),
                equals([$plugin1, $plugin2, $plugin3])
        );
    }

    /**
     * @test
     */
    public function bindTypedListMoreThanOnceAddsToSameList(): void
    {
        $plugin1 = NewInstance::of(Plugin::class);
        $plugin2 = NewInstance::of(Plugin::class);
        $plugin3 = NewInstance::of(Plugin::class);
        $binder  = new Binder();
        $binder->bindList('listConfig');
        $binder->bindMap('mapConfig');
        $binder->bindList(Plugin::class)
                ->withValue($plugin1);
        $binder->bindList(Plugin::class)
                ->withValueFromProvider($this->createProviderForValue($plugin2));
        $binder->bindList(Plugin::class)
                ->withValueFromClosure(
                        function() use($plugin3) { return $plugin3; }
                );
        $pluginHandler = $this->createPluginHandler($binder);
        assertThat(
                $pluginHandler->getPluginList(),
                equals([$plugin1, $plugin2, $plugin3])
        );
    }

    /**
     * @test
     */
    public function createTypedMap(): void
    {
        $plugin1 = NewInstance::of(Plugin::class);
        $plugin2 = NewInstance::of(Plugin::class);
        $plugin3 = NewInstance::of(Plugin::class);
        $binder  = new Binder();
        $binder->bindList('listConfig');
        $binder->bindMap('mapConfig');
        $binder->bindMap(Plugin::class)
                ->withEntry('tb', $plugin1)
                ->withEntryFromProvider(
                        'dd',
                        $this->createProviderForValue($plugin2)
                )
                ->withEntryFromClosure(
                        'hf',
                        function() use($plugin3) { return $plugin3; }
                );
        $pluginHandler = $this->createPluginHandler($binder);
        assertThat(
                $pluginHandler->getPluginMap(),
                equals(['tb' => $plugin1, 'dd' => $plugin2, 'hf' => $plugin3])
        );
    }

    /**
     * @test
     */
    public function bindTypedMapMoreThanOnceAddsToSameList(): void
    {
        $plugin1 = NewInstance::of(Plugin::class);
        $plugin2 = NewInstance::of(Plugin::class);
        $plugin3 = NewInstance::of(Plugin::class);
        $binder  = new Binder();
        $binder->bindList('listConfig');
        $binder->bindMap('mapConfig');
        $binder->bindMap(Plugin::class)
                ->withEntry('tb', $plugin1);
        $binder->bindMap(Plugin::class)
                ->withEntryFromProvider(
                        'dd',
                        $this->createProviderForValue($plugin2)
                );
        $binder->bindMap(Plugin::class)
                ->withEntryFromClosure(
                        'hf',
                        function() use($plugin3) { return $plugin3; }
                );
        $pluginHandler = $this->createPluginHandler($binder);
        assertThat(
                $pluginHandler->getPluginMap(),
                equals(['tb' => $plugin1, 'dd' => $plugin2, 'hf' => $plugin3])
        );
    }

    /**
     * @test
     */
    public function typedListWithInvalidValueThrowsBindingException(): void
    {
        $binder = new Binder();
        $binder->bindList('listConfig');
        $binder->bindMap('mapConfig');
        $binder->bindList(Plugin::class)->withValue(303);
        expect(function() use ($binder) {
                $this->createPluginHandler($binder);
        })->throws(BindingException::class);
    }

    /**
     * @test
     */
    public function typedMapWithInvalidValueThrowsBindingException(): void
    {
        $binder = new Binder();
        $binder->bindList('listConfig');
        $binder->bindMap('mapConfig');
        $binder->bindMap(Plugin::class)->withEntry('tb', 303);
        expect(function() use ($binder) {
                $this->createPluginHandler($binder);
        })->throws(BindingException::class);
    }

    /**
     * @test
     */
    public function mixedAnnotations(): void
    {
        $plugin = NewInstance::of(Plugin::class);
        $binder = new Binder();
        $binder->bindList('listConfig');
        $binder->bindMap('mapConfig');
        $binder->bind(Plugin::class)
               ->named('foo')
               ->toInstance($plugin);
        $binder->bindConstant('foo')
               ->to(42);
        $binder->bindList('aList')
               ->withValue(313);
        $binder->bindMap('aMap')
               ->withEntry('tb', 303);
        assertThat(
                $this->createPluginHandler($binder)->getArgs(),
                equals([
                        'std'    => $plugin,
                        'answer' => 42,
                        'list'   => [313],
                        'map'    => ['tb' => 303]
                ])
        );
    }

    /**
     * creates mocked provider
     *
     * @param   mixed  $value
     * @return  \stubbles\ioc\InjectionProvider
     */
    private function createProviderForValue($value): InjectionProvider
    {
        return NewInstance::of(InjectionProvider::class)
                ->returns(['get' => $value]);
    }

    /**
     * creates plugin handler instance
     *
     * @param   Binder  $binder
     * @return  PluginHandler
     */
    private function createPluginHandler(Binder $binder): PluginHandler
    {
        return $binder->getInjector()->getInstance(PluginHandler::class);
    }
}
