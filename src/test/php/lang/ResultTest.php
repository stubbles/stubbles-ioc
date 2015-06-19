<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles
 */
namespace stubbles\lang;
/**
 * Tests for stubbles\lang\Result.
 *
 * @since  6.0.0
 * @group  lang
 * @group  lang_core
 */
class ResultTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function resultOfNullIsAlwaysSame()
    {
        assertSame(
                Result::of(null),
                Result::of(null)
        );
    }

    /**
     * @test
     */
    public function resultOfNullMeansResultNotPresent()
    {
        assertFalse(Result::of(null)->isPresent());
    }

    /**
     * @test
     */
    public function resultOfNonNullMeansResultPresent()
    {
        assertTrue(Result::of(303)->isPresent());
    }

    /**
     * @test
     */
    public function valueReturnsResultValue()
    {
        assertEquals(303, Result::of(303)->value());
    }

    /**
     * @test
     */
    public function filterOnResultOfNullReturnsResultOfNull()
    {
        assertSame(
                Result::of(null),
                Result::of(null)->filter(function($value) { return true; })
        );
    }

    /**
     * @test
     */
    public function filterOnResultOfNonNullReturnsResultOfNullWhenPredicateDenies()
    {
        assertSame(
                Result::of(null),
                Result::of(303)->filter(function($value) { return false; })
        );
    }

    /**
     * @test
     */
    public function filterOnResultOfNonNullReturnsResultWhenPredicateApproves()
    {
        $result = Result::of(303);
        assertSame(
                $result,
                $result->filter(function($value) { return true; })
        );
    }

    /**
     * @test
     */
    public function mapResultOfNullReturnsResultOfNull()
    {
        assertSame(
                Result::of(null),
                Result::of(null)->map(function($value) { return 909; })
        );
    }

    /**
     * @test
     */
    public function mapResultOfNonNullReturnsMappedResult()
    {
        assertEquals(
                Result::of(909),
                Result::of(303)->map(function($value) { return 909; })
        );
    }

    /**
     * @test
     */
    public function whenNullOnResultOfNullReturnsOther()
    {
        assertEquals(909, Result::of(null)->whenNull(909)->value());
    }

    /**
     * @test
     */
    public function whenNullOnResultOfNonNullReturnsValue()
    {
        assertEquals(303, Result::of(303)->whenNull(909)->value());
    }

    /**
     * @test
     */
    public function applyhenNullOnResultOfNullReturnsOther()
    {
        assertEquals(
                909,
                Result::of(null)
                        ->applyWhenNull(function() { return 909; })
                        ->value()
        );
    }

    /**
     * @test
     */
    public function applyWhenNullOnResultOfNonNullReturnsValue()
    {
        assertEquals(
                303,
                Result::of(303)
                        ->applyWhenNull(function() { return 909; })
                        ->value()
        );
    }

    /**
     * @return  array
     */
    public function emptyValues()
    {
        return [[null], [''], [[]]];
    }

    /**
     * @param  mixed  $value
     * @test
     * @dataProvider  emptyValues
     * @since  6.2.0
     */
    public function isEmptyForEmptyValues($value)
    {
        assertTrue(Result::of($value)->isEmpty());
    }

    /**
     * @return  array
     */
    public function nonEmptyValues()
    {
        return [[0], [303], ['foo'], [['foo']]];
    }

    /**
     * @param  mixed  $value
     * @test
     * @dataProvider  nonEmptyValues
     * @since  6.2.0
     */
    public function isNotEmptyForNomEmptyValues($value)
    {
        assertFalse(Result::of($value)->isEmpty());
    }

    /**
     * @param  mixed  $value
     * @test
     * @dataProvider  emptyValues
     * @since  6.2.0
     */
    public function whenEmptyOnResultOfEmptyReturnsOther($value)
    {
        assertEquals(909, Result::of($value)->whenEmpty(909)->value());
    }

    /**
     * @param  mixed  $value
     * @test
     * @dataProvider  nonEmptyValues
     * @since  6.2.0
     */
    public function whenEmptyOnResultOfNonEmptyReturnsValue($value)
    {
        assertEquals($value, Result::of($value)->whenEmpty(909)->value());
    }

    /**
     * @param  mixed  $value
     * @test
     * @dataProvider  emptyValues
     * @since  6.2.0
     */
    public function applyhenEmptyOnResultOfEmptyReturnsOther($value)
    {
        assertEquals(
                909,
                Result::of($value)
                        ->applyWhenEmpty(function() { return 909; })
                        ->value()
        );
    }

    /**
     * @param  mixed  $value
     * @test
     * @dataProvider  nonEmptyValues
     * @since  6.2.0
     */
    public function applyWhenEmptyOnResultOfNonEmptyReturnsValue($value)
    {
        assertEquals(
                $value,
                Result::of($value)
                        ->applyWhenEmpty(function() { return 909; })
                        ->value()
        );
    }
}
