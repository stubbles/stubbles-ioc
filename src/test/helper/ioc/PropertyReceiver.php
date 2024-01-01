<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\test\ioc;
/**
 * Helper class for the test.
 */
class PropertyReceiver
{
    /**
     * @Property{foo}('example.foo')
     * @Property{bar}('example.bar')
     */
    public function __construct(public string $foo, public $bar) { }
}
