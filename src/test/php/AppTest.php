<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use stubbles\ioc\Binder;
use stubbles\ioc\Injector;
use stubbles\test\AppClassWithBindings;
use stubbles\test\AppClassWithInvalidBindingModule;
use stubbles\test\AppClassWithoutBindings;
use stubbles\test\AppUsingBindingModule;

use function bovigo\assert\assertThat;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isInstanceOf;
use function bovigo\assert\predicate\isSameAs;
/**
 * Test for stubbles\App.
 */
#[Group('app')]
class AppTest extends TestCase
{
    protected function tearDown(): void
    {
        \restore_error_handler();
        \restore_exception_handler();
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function createCreatesInstanceUsingBindings(): void
    {
        $appCommandWithBindings = AppClassWithBindings::create('projectPath');
        assertThat($appCommandWithBindings, isInstanceOf(AppClassWithBindings::class));
    }

    #[Test]
    public function createInstanceCreatesInstanceUsingBindings(): void
    {
        $appCommandWithBindings = App::createInstance(
            AppClassWithBindings::class,
            'projectPath'
        );
        assertThat($appCommandWithBindings, isInstanceOf(AppClassWithBindings::class));
    }

    #[Test]
    public function createInstanceCreatesInstanceWithoutBindings(): void
    {
        assertThat(
            App::createInstance(
                AppClassWithoutBindings::class,
                'projectPath'
            ),
            isInstanceOf(AppClassWithoutBindings::class)
        );
    }

    /**
     * @since  5.0.0
     */
    #[Test]
    public function projectPathIsBoundWithExplicitBindings(): void
    {
        assertThat(
            AppClassWithBindings::create('projectPath')->pathOfProject,
            equals('projectPath')
        );
    }

    /**
     * @since  5.0.0
     */
    #[Test]
    public function projectPathIsBoundWithoutExplicitBindings(): void
    {
        assertThat(
            AppClassWithoutBindings::create('projectPath')->pathOfProject,
            equals('projectPath')
        );
    }

    /**
     * @since  2.0.0
     */
    #[Test]
    public function canCreateRuntime(): void
    {
        assertThat(
            AppUsingBindingModule::callBindRuntime(),
            isInstanceOf(Runtime::class)
        );
    }

    /**
     * @since  2.1.0
     */
    #[Test]
    #[Group('issue_33')]
    public function dynamicBindingViaClosure(): void
    {
        assertThat(
            AppClassWithBindings::create('projectPath')->wasBoundBy(),
            equals('closure')
        );
    }

    /**
     * @since  3.4.0
     */
    #[Test]
    public function bindCurrentWorkingDirectory(): void
    {
        $binder = new Binder();
        $module = AppUsingBindingModule::currentWorkingDirectoryModule();
        $module($binder);
        assertTrue($binder->getInjector()->hasConstant('stubbles.cwd'));
    }

    /**
     * @since  3.4.0
     */
    #[Test]
    #[TestWith(['stubbles.hostname.nq'])]
    #[TestWith(['stubbles.hostname.fq'])]
    public function bindHostname(string $key): void
    {
        $binder = new Binder();
        $module = AppUsingBindingModule::bindHostnameModule();
        $module($binder);
        assertTrue($binder->getInjector()->hasConstant($key));
    }

    #[Test]
    public function invalidBindingModuleThrowsIllegalArgumentException(): void
    {
        expect(function() {
            App::createInstance(
                AppClassWithInvalidBindingModule::class,
                'projectPath'
            );
        })->throws(\InvalidArgumentException::class);
    }

    /**
     * @return  array<callable[]>
     */
    public static function assertions(): array
    {
        return [
            [function(Injector $injector) { assertTrue($injector->hasBinding('foo')); }],
            [function(Injector $injector) { assertTrue($injector->hasBinding('bar')); }],
            [function(Injector $injector) { assertTrue($injector->hasBinding(Injector::class)); }],
            [function(Injector $injector) { assertThat($injector->getInstance(Injector::class), isSameAs($injector)); }]
        ];
    }

    #[Test]
    #[DataProvider('assertions')]
    public function bindingModulesAreProcessed(callable $assertion): void
    {
        $injector = App::createInstance(
            AppClassWithBindings::class,
            'projectPath'
        )->injector;
        $assertion($injector);
    }
}
