<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\ioc\binding;
use stubbles\values\Secret;
/**
 * Class used for tests.
 *
 * @since  4.1.3
 */
class Example
{
    /**
     * @Property('example.password')
     */
    public function __construct(public ?Secret $password) { }
}