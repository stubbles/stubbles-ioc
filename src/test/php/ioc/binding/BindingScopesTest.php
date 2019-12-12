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

use function bovigo\assert\assertThat;
use function bovigo\assert\predicate\isInstanceOf;
use function bovigo\assert\predicate\isSameAs;
/**
 * Test for stubbles\ioc\binding\BindingScopes
 *
 * @group  ioc
 * @group  ioc_binding
 */
class BindingScopesTest extends TestCase
{
    /**
     * @test
     */
    public function createsSingletonScopeIfNonePassed(): void
    {
        $bindingScopes = new BindingScopes();
        assertThat(
                $bindingScopes->singleton(),
                isInstanceOf(SingletonBindingScope::class)
        );
    }

    /**
     * @test
     */
    public function usesPassedSingletonScope(): void
    {
        $singletonScope = NewInstance::of(BindingScope::class);
        $bindingScopes  = new BindingScopes($singletonScope);
        assertThat($bindingScopes->singleton(), isSameAs($singletonScope));
    }

    /**
     * @test
     */
    public function usesPassedSessionScope(): void
    {
        $sessionScope  = NewInstance::of(BindingScope::class);
        $bindingScopes = new BindingScopes(null, $sessionScope);
        assertThat($bindingScopes->session(), isSameAs($sessionScope));
    }
}
