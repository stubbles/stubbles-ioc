<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  net\stubbles
 */
namespace net\stubbles\ioc;
use net\stubbles\lang\BaseObject;
use net\stubbles\lang\reflect\ReflectionClass;
/**
 * Helper class for the test.
/**
 * Test for net\stubbles\ioc\Injector with constant binding.
 *
 * @group  ioc
 */
class InjectorConstantTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * combined helper assertion for the test
     *
     * @param  Injector  $injector
     */
    protected function assertConstantInjection(Injector $injector)
    {
        $question = $injector->getInstance('org\\stubbles\\test\\ioc\\Question');
        $this->assertInstanceOf('org\\stubbles\\test\\ioc\\Question', $question);
        $this->assertEquals(42, $question->getAnswer());
    }

    /**
     * @test
     */
    public function injectConstant()
    {
        $binder = new Binder();
        $binder->bindConstant()->named('answer')->to(42);
        $this->assertConstantInjection($binder->getInjector());
    }

    /**
     * @test
     */
    public function checkForNonExistingConstantReturnsFalse()
    {
        $binder = new Binder();
        $this->assertFalse($binder->getInjector()->hasConstant('answer'));
    }

    /**
     * @test
     * @expectedException  net\stubbles\ioc\BindingException
     */
    public function retrieveNonExistingConstantThrowsBindingException()
    {
        $binder = new Binder();
        $binder->getInjector()->getConstant('answer');
    }

    /**
     * @test
     * @since  1.1.0
     */
    public function checkForExistingConstantReturnsTrue()
    {
        $binder = new Binder();
        $binder->bindConstant()->named('answer')->to(42);
        $this->assertTrue($binder->getInjector()->hasConstant('answer'));
    }

    /**
     * @test
     * @since  1.1.0
     */
    public function retrieveExistingConstantReturnsValue()
    {
        $binder = new Binder();
        $binder->bindConstant()->named('answer')->to(42);
        $this->assertEquals(42, $binder->getInjector()->getConstant('answer'));
    }

    /**
     * @test
     * @group  ioc_constantprovider
     * @since  1.6.0
     */
    public function constantViaInjectionProviderInstance()
    {
        $binder = new Binder();
        $binder->bindConstant()
               ->named('answer')
               ->toProvider(new ValueInjectionProvider(42));
        $injector = $binder->getInjector();
        $this->assertTrue($injector->hasConstant('answer'));
        $this->assertEquals(42, $injector->getConstant('answer'));
        $this->assertConstantInjection($binder->getInjector());
    }

    /**
     * @test
     * @expectedException  net\stubbles\ioc\BindingException
     * @group              ioc_constantprovider
     * @since              1.6.0
     */
    public function constantViaInvalidInjectionProviderClassThrowsBindingException()
    {
        $binder = new Binder();
        $binder->bindConstant()
               ->named('answer')
               ->toProviderClass('\stdClass');
        $binder->getInjector()->getConstant('answer');
    }

    /**
     * @test
     * @group  ioc_constantprovider
     * @since  1.6.0
     */
    public function constantViaInjectionProviderClass()
    {
        $binder = new Binder();
        $binder->bindConstant()
               ->named('answer')
               ->toProviderClass(new ReflectionClass('org\\stubbles\\test\\ioc\\AnswerConstantProvider'));
        $injector = $binder->getInjector();
        $this->assertTrue($injector->hasConstant('answer'));
        $this->assertEquals(42, $injector->getConstant('answer'));
        $this->assertConstantInjection($binder->getInjector());
    }

    /**
     * @test
     * @group  ioc_constantprovider
     * @since  1.6.0
     */
    public function constantViaInjectionProviderClassName()
    {
        $binder = new Binder();
        $binder->bindConstant()
               ->named('answer')
               ->toProviderClass('org\\stubbles\\test\\ioc\\AnswerConstantProvider');
        $injector = $binder->getInjector();
        $this->assertTrue($injector->hasConstant('answer'));
        $this->assertEquals(42, $injector->getConstant('answer'));
        $this->assertConstantInjection($binder->getInjector());
    }
}
?>