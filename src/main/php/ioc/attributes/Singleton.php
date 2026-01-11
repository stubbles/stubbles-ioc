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
namespace stubbles\ioc\attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Singleton
{
    // intentionally empty
}
