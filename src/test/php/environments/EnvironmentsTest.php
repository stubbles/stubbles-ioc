<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\environments;
use PHPUnit\Framework\TestCase;

use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
/**
 * Tests for stubbles\environments.
 *
 * All tests that do not require restoring the error or exception handler.
 *
 * @group  environments
 */
class EnvironmentsTest extends TestCase
{
    /**
     * @test
     */
    public function cacheIsEnabledInProduction(): void
    {
        assertTrue((new Production())->isCacheEnabled());
    }

    /**
     * @test
     */
    public function cacheIsDisabledInDevelopment(): void
    {
        assertFalse((new Development())->isCacheEnabled());
    }

    /**
     * @test
     */
    public function developmentHasNoErrorHandlerByDefault(): void
    {
        assertFalse((new Development())->registerErrorHandler('/tmp'));
    }
}
