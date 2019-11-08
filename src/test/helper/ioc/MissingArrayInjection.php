<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\test\ioc;
/**
 * Test class for missing array injection.
 *
 * @since  2.0.0
 */
class MissingArrayInjection
{
    private $data;
    /**
     * constructor
     *
     * @param  array  $data
     * @Named('foo')
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
