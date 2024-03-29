<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles
 */
namespace stubbles\ioc\module;
use stubbles\ioc\Binder;
/**
 * Interface for modules which configure the binder.
 *
 * @api
 */
interface BindingModule
{
    /**
     * configure the binder
     */
    public function configure(Binder $binder, string $projectPath): void;
}
