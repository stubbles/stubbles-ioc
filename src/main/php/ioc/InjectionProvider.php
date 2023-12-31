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
namespace stubbles\ioc;
/**
 * Interface for providers that create objects that are required by the
 * Inversion of Control features of Stubbles.
 *
 * @api
 */
interface InjectionProvider
{
    /**
     * returns the value to provide
     */
    public function get(string $name = null): mixed;
}
