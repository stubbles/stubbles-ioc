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
namespace stubbles;
/**
 * Defines an environment where the application is running in.
 *
 * The environment instance contains information about which exception handler
 * and which error handler should be used, else well as whether caching is
 * enabled or not.
 */
interface Environment
{
    /**
     * constant for enabled cache
     */
    const CACHE_ENABLED  = true;
    /**
     * constant for disabled cache
     */
    const CACHE_DISABLED = false;

    /**
     * returns the name of the environment
     *
     * @api
     * @return  string
     */
    public function name(): string;

    /**
     * registers exception handler for current environment
     *
     * Return value depends on registration: if no exception handler set return
     * value will be false, if registered handler was an instance the handler
     * instance will be returned, and true in any other case.
     *
     * @param   string       $projectPath  path to project
     * @return  bool|object
     */
    public function registerExceptionHandler(string $projectPath);

    /**
     * registers error handler for current environment
     *
     * Return value depends on registration: if no error handler set return value
     * will be false, if registered handler was an instance the handler instance
     * will be returned, and true in any other case.
     *
     * @param   string       $projectPath  path to project
     * @return  bool|object
     */
    public function registerErrorHandler(string $projectPath);

    /**
     * checks whether cache is enabled or not
     *
     * @api
     * @return  bool
     */
    public function isCacheEnabled(): bool;
}
