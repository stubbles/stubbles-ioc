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
namespace stubbles\ioc\binding;
/**
 * Basic interface for sessions for session-scoped bindings.
 *
 * @api
 * @since  5.4.0
 */
interface Session
{
    /**
     * checks if session contains value under given key
     */
    public function hasValue(string $key): bool;

    /**
     * returns value stored under given key
     */
    public function value(string $key, mixed $default = null): mixed;

    /**
     * stores given value under given key
     */
    public function putValue(string $key, mixed $value): void;
}
