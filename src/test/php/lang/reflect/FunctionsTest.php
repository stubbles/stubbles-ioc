<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles
 */
namespace stubbles\lang\reflect;
/**
 * Tests for stubbles\lang\reflect\*().
 *
 * @since  5.3.0
 * @group  lang
 * @group  lang_reflect
 */
class FunctionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function annotationsWithMethodNameReturnsMethodAnnotations()
    {
        $this->assertEquals(
                __CLASS__ . '::' . __FUNCTION__ . '()',
                annotationsOf(__CLASS__, __FUNCTION__)->target()
        );
    }

    /**
     * @test
     */
    public function annotationsWithClassNameReturnsClassAnnotations()
    {
        $this->assertEquals(
                __CLASS__,
                annotationsOf(__CLASS__)->target()
        );
    }

    /**
     * @test
     */
    public function constructorAnnotationsWithClassNameReturnsConstructorAnnotations()
    {
        $this->assertEquals(
                'PHPUnit_Framework_TestCase::__construct()',
                constructorAnnotationsOf(__CLASS__)->target()
        );
    }

    /**
     * @test
     */
    public function annotationsWithClassInstanceReturnsClassAnnotations()
    {
        $this->assertEquals(
                __CLASS__,
                annotationsOf($this)->target()
        );
    }

    /**
     * @test
     */
    public function constructorAnnotationsWithClassInstanceReturnsConstructorAnnotations()
    {
        $this->assertEquals(
                'PHPUnit_Framework_TestCase::__construct()',
                constructorAnnotationsOf($this)->target()
        );
    }

    /**
     * @test
     */
    public function annotationsWithFunctionNameReturnsFunctionAnnotations()
    {
        $this->assertEquals(
                'stubbles\lang\reflect\annotationsOf()',
                annotationsOf('stubbles\lang\reflect\annotationsOf')->target()
        );
    }

    /**
     * @test
     * @expectedException  ReflectionException
     */
    public function annotationsWithUnknownClassAndFunctionNameThrowsReflectionException()
    {
        annotationsOf('doesNotExist');
    }

    /**
     * @param  string  $refParam
     */
    private function example($refParam)
    {

    }

    /**
     * @test
     */
    public function annotationsWithReflectionParameterReturnsParameterAnnotations()
    {
        $refParam = new \ReflectionParameter([$this, 'example'], 'refParam');
        $this->assertEquals(
                __CLASS__ . '::example()#refParam',
                annotationsOf($refParam)->target()
        );
    }

    /**
     * @type  null
     */
    private $someProperty;
    /**
     *
     * @type  null
     */
    private static $otherProperty;

    /**
     * @return  array
     */
    public function properties()
    {
        return [['->', 'someProperty'], ['::$', 'otherProperty']];
    }

    /**
     * @test
     * @dataProvider  properties
     */
    public function annotationsWithReflectionPropertyReturnsPropertyAnnotations($connector, $propertyName)
    {
        $refProperty = new \ReflectionProperty($this, $propertyName);
        $this->assertEquals(
                __CLASS__ . $connector . $propertyName,
                annotationsOf($refProperty)->target()
        );
    }

    /**
     * @test
     * @expectedException  ReflectionException
     */
    public function annotationTargetThrowsReflectionExceptionForNonSupportedAnnotationPlaces()
    {
        _annotationTarget(new \ReflectionExtension('date'));
    }

    /**
     * @test
     * @expectedException  ReflectionException
     */
    public function docCommentThrowsReflectionExceptionForNonSupportedAnnotationPlaces()
    {
        docComment(new \ReflectionExtension('date'));
    }
}
