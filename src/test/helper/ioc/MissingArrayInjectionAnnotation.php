<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\test\ioc;
/**
 * Test class for missing array injection.
 *
 * @deprecated will be removed with 13.0.0
 */
class MissingArrayInjectionAnnotation
{
    /**
     * @param  array  $data
     * @Named('foo')
     */
    public function __construct(private array $data) { }
}
