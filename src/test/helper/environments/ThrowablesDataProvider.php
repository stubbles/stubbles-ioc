<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\test\environments;

use Error;
use Exception;
use Generator;

class ThrowablesDataProvider
{
    public static function throwables(): Generator
    {
        yield [new Exception('failure message')];
        yield [new Error('failure message')];
    }

    public static function file(): string
    {
        return __FILE__;
    }
}
