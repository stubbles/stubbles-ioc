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
class AppClassWithoutBindings extends App
{
    /**
     * @Named('stubbles.project.path')
     */
    public function __construct(public string $pathOfProject) { }

    /**
     * runs the command
     */
    public function run(): void { }
}
