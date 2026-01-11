<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\test\ioc;
/**
 * Helper class for the test
 *
 * @ProvidedBy(stubbles\test\ioc\InjectorProvidedByProvider.class)
 * @deprecated will be removed with 13.0.0
 */
interface Person2Annotated
{
    public function sayHello2(): string;
}
