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
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertThat;
use function bovigo\assert\predicate\isInstanceOf;
use function bovigo\assert\predicate\isSameAs;
/**
 * Test for stubbles\ioc\binding\BindingScopes
 */
#[Group('ioc')]
#[Group('ioc_binding')]
class BindingScopesTest extends TestCase
{
    #[Test]
    public function createsSingletonScopeIfNonePassed(): void
    {
        $bindingScopes = new BindingScopes();
        assertThat(
            $bindingScopes->singleton(),
            isInstanceOf(SingletonBindingScope::class)
        );
    }

    #[Test]
    public function usesPassedSingletonScope(): void
    {
        $singletonScope = NewInstance::of(BindingScope::class);
        $bindingScopes  = new BindingScopes($singletonScope);
        assertThat($bindingScopes->singleton(), isSameAs($singletonScope));
    }

    #[Test]
    public function usesPassedSessionScope(): void
    {
        $sessionScope  = NewInstance::of(BindingScope::class);
        $bindingScopes = new BindingScopes(null, $sessionScope);
        assertThat($bindingScopes->session(), isSameAs($sessionScope));
    }
}
