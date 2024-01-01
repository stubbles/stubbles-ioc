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
use stubbles\test\ioc\Mikey;
use stubbles\test\ioc\Person2;
use stubbles\test\ioc\Schst;

use function bovigo\assert\assertThat;
use function bovigo\assert\predicate\isInstanceOf;
/**
 * Test for stubbles\ioc\Injector with the ProvidedBy annotation.
 */
#[Group('ioc')]
class InjectorProvidedByTest extends TestCase
{
    #[Test]
    #[Group('bug226')]
    public function annotatedProviderClassIsUsedWhenNoExplicitBindingSpecified(): void
    {
        assertThat(
            Binder::createInjector()->getInstance(Person2::class),
            isInstanceOf(Schst::class)
        );
    }

    #[Test]
    public function explicitBindingOverwritesProvidedByAnnotation(): void
    {
        $binder = new Binder();
        $binder->bind(Person2::class)->to(Mikey::class);
        assertThat(
            $binder->getInjector()->getInstance(Person2::class),
            isInstanceOf(Mikey::class)
        );
    }
}
