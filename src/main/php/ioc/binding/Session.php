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
     *
     * @param   string  $key
     * @return  bool
     */
    public function hasValue(string $key): bool;

    /**
     * returns value stored under given key
     *
     * @param   string  $key
     * @return  mixed
     */
    public function value(string $key);

    /**
     * stores given value under given key
     *
     * @param  string  $key
     * @param  mixed   $value
     */
    public function putValue(string $key, $value): void;
}
