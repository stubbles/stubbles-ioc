<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\test;
use stubbles\ioc\Binder;
use stubbles\ioc\module\BindingModule;
/**
 * Helper class for ioc tests.
 */
class AppTestBindingModuleOne implements BindingModule
{
    /**
     * configure the binder
     */
    public function configure(Binder $binder, string $projectPath = null): void
    {
        $binder->bind('foo')->to('\\stdClass');
    }
}
