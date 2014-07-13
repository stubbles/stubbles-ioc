<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles
 */
namespace stubbles\lang\iterator;
/**
 * Tests for stubbles\lang\iterator\Mapper.
 *
 * @group  lang
 * @group  lang_iterator
 * @group  sequence
 * @since  4.1.0
 */
class MapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function peekCallsConsumerWithCurrentValueOnIteration()
    {
        $mapper = new Mapper(new \ArrayIterator(['foo', 'bar', 'baz']), function($value) { $this->assertEquals(3, strlen($value)); return 'mapped'; });
        foreach ($mapper as $value) {
            $this->assertEquals('mapped', $value);
        }
    }

}
