<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\test\ioc;
/**
 * Helper class for the test.
 *
 * @deprecated will be removed with 13.0.0
 */
class DevelopersMultipleConstructorParamsWithConstantAnnotation
{
    /**
     * constructor method with Named() annotation on a specific param
     *
     * @Named{role}('boss')
     */
    public function __construct(public Employee $schst, public string $role) { }
}
