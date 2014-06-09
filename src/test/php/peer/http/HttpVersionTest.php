<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles
 */
namespace stubbles\peer\http;
/**
 * Test for stubbles\peer\http\HttpVersion.
 *
 * @since  4.0.0
 * @group  peer
 * @group  peer_http
 */
class HttpVersionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return  array
     */
    public function emptyVersions()
    {
        return [[''], [null]];
    }

    /**
     * @test
     * @expectedException  stubbles\lang\exception\IllegalArgumentException
     * @expectedExceptionMessage  Given HTTP version is empty
     * @dataProvider  emptyVersions
     */
    public function parseFromStringThrowsIllegalArgumentExceptionWhenGivenVersionIsEmpty($empty)
    {
        HttpVersion::fromString($empty);
    }

    /**
     * @test
     * @expectedException  stubbles\lang\exception\IllegalArgumentException
     * @expectedExceptionMessage  Given HTTP version "invalid" can not be parsed
     */
    public function parseFromStringThrowsIllegalArgumentExceptionWhenParsingFails()
    {
        HttpVersion::fromString('invalid');
    }

    /**
     * @test
     */
    public function fromStringDetectsCorrectMajorVersion()
    {
        $this->assertEquals(1, HttpVersion::fromString('HTTP/1.2')->major());
    }

    /**
     * @test
     */
    public function fromStringDetectsCorrectMinorVersion()
    {
        $this->assertEquals(2, HttpVersion::fromString('HTTP/1.2')->minor());
    }

    /**
     * @test
     * @expectedException  stubbles\lang\exception\IllegalArgumentException
     * @expectedExceptionMessage  Given major version "foo" is not an integer
     */
    public function constructWithInvalidMajorArgumentThrowsIllegalArgumentException()
    {
        new HttpVersion('foo', 1);
    }

    /**
     * @test
     * @expectedException  stubbles\lang\exception\IllegalArgumentException
     * @expectedExceptionMessage  Given minor version "foo" is not an integer
     */
    public function constructWithInvalidMinorArgumentThrowsIllegalArgumentException()
    {
        new HttpVersion(1, 'foo');
    }

    /**
     * @test
     * @expectedException  stubbles\lang\exception\IllegalArgumentException
     * @expectedExceptionMessage  Major version can not be negative
     */
    public function constructWithNegativeMajorVersionThrowsIllegalArgumentException()
    {
        new HttpVersion(-2, 1);
    }

    /**
     * @test
     * @expectedException  stubbles\lang\exception\IllegalArgumentException
     * @expectedExceptionMessage  Major version can not be negative
     */
    public function parseFromStringWithNegativeMajorNumberThrowsIllegalArgumentExceptionWhenParsingFails()
    {
        HttpVersion::fromString('HTTP/-2.1');
    }

    /**
     * @test
     * @expectedException  stubbles\lang\exception\IllegalArgumentException
     * @expectedExceptionMessage  Minor version can not be negative
     */
    public function constructWithNegativeMinorVersionThrowsIllegalArgumentException()
    {
        new HttpVersion(1, -2);
    }

    /**
     * @test
     * @expectedException  stubbles\lang\exception\IllegalArgumentException
     * @expectedExceptionMessage  Minor version can not be negative
     */
    public function parseFromStringWithNegativeMinorNumberThrowsIllegalArgumentExceptionWhenParsingFails()
    {
        HttpVersion::fromString('HTTP/2.-1');
    }

    /**
     * @test
     */
    public function castToStringReturnsCorrectVersionString()
    {
        $versionString = 'HTTP/1.1';
        $this->assertEquals(
                $versionString,
                (string) HttpVersion::fromString($versionString)
        );
    }

     /**
     * @test
     * @expectedException  stubbles\lang\exception\IllegalArgumentException
     * @expectedExceptionMessage  Given HTTP version is empty
     * @dataProvider  emptyVersions
     */
    public function castFromEmptyWithoutDefaultThrowsIllegalArgumentException($empty)
    {
        HttpVersion::castFrom($empty);
    }

     /**
     * @test
     * @dataProvider  emptyVersions
     */
    public function castFromEmptyWithDefaultReturnsDefault($empty)
    {
        $this->assertEquals(
                new HttpVersion(1, 1),
                HttpVersion::castFrom($empty, 'HTTP/1.1')
        );
    }

     /**
     * @test
     * @dataProvider  emptyVersions
     */
    public function castFromEmptyWithDefaultInstanceReturnsDefaultInstance($empty)
    {
        $httpVersion = new HttpVersion(1, 1);
        $this->assertSame(
                $httpVersion,
                HttpVersion::castFrom($empty, $httpVersion)
        );
    }

    /**
     * @test
     */
    public function castFromInstanceReturnsInstance()
    {
        $httpVersion = new HttpVersion(1, 1);
        $this->assertSame($httpVersion, HttpVersion::castFrom($httpVersion));
    }

    /**
     * @test
     */
    public function castFromInstanceWithReturnsInstance()
    {
        $httpVersion = new HttpVersion(1, 1);
        $this->assertSame($httpVersion, HttpVersion::castFrom($httpVersion, new HttpVersion(1, 0)));
    }

    /**
     * @test
     */
    public function castFromStringReturnsInstance()
    {
        $this->assertEquals(
                new HttpVersion(1, 1),
                HttpVersion::castFrom('HTTP/1.1')
        );
    }

    /**
     * @test
     */
    public function castFromStringWithReturnsInstance()
    {
        $this->assertEquals(
                new HttpVersion(1, 1),
                HttpVersion::castFrom('HTTP/1.1', new HttpVersion(1, 0))
        );
    }
}
