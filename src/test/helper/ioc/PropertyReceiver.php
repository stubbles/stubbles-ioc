<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\test\ioc;

use stubbles\ioc\attributes\Property;

/**
 * Helper class for the test.
 */
class PropertyReceiver
{
    public function __construct(
        #[Property('example.foo')] public string $foo,
        #[Property('example.bar')] public $bar
    ) { }
}
