<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\test;
use stubbles\App;
/**
 * Helper class for ioc tests.
 */
class AppClassWithInvalidBindingModule extends App
{
    /**
     * return list of bindings required for this command
     *
     * @return  array<BindingModule|Closure>
     */
    public static function __bindings(): array
    {
        return ['\stdClass'];
    }
    /**
     * runs the command
     */
    public function run(): void { }
}
