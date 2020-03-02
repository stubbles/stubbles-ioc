<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles;
use bovigo\callmap\NewInstance;
use PHPUnit\Framework\TestCase;
use stubbles\ioc\Binder;
use stubbles\environments\Production;
use org\bovigo\vfs\vfsStream;

use function bovigo\assert\assertThat;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
use function bovigo\assert\expect;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isInstanceOf;
use function bovigo\assert\predicate\isSameAs;
use function bovigo\callmap\verify;
/**
 * Test for stubbles\ioc\module\Runtime.
 *
 * @group  app
 */
class RuntimeTest extends TestCase
{
    /**
     * mocked mode instance
     *
     * @var  Environment&\bovigo\callmap\ClassProxy
     */
    private $environment;
    /**
     * root path
     *
     * @var  \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    protected function setUp(): void
    {
        $this->root        = vfsStream::setup('projects');
        $this->environment = NewInstance::of(Environment::class)
                ->returns(['name' => 'TEST']);
        Runtime::reset();
    }

    protected function tearDown(): void
    {
        Runtime::reset();
    }

    /**
     * @test
     * @since  5.0.0
     */
    public function runtimeIsNotInitializedWhenNoInstanceCreated(): void
    {
        assertFalse(Runtime::initialized());
    }

    /**
     * @test
     * @since  5.0.0
     */
    public function runtimeIsInitializedAfterFirstInstanceCreation(): void
    {
        new Runtime();
        assertTrue(Runtime::initialized());
    }

    /**
     * @test
     */
    public function registerMethodsShouldBeCalledWithGivenProjectPath(): void
    {
        $runtime = new Runtime($this->environment);
        $runtime->configure(new Binder(), $this->root->url());
        verify($this->environment, 'registerErrorHandler')
                ->received($this->root->url());
        verify($this->environment, 'registerExceptionHandler')
                ->received($this->root->url());
    }

    /**
     * @test
     */
    public function givenEnvironmentShouldBeBound(): void
    {
        $runtime = new Runtime($this->environment);
        $binder  = new Binder();
        $runtime->configure($binder, $this->root->url());
        assertThat(
                $binder->getInjector()->getInstance(Environment::class),
                isSameAs($this->environment)
        );
    }

    /**
     * @test
     */
    public function noEnvironmentGivenDefaultsToProdEnvironment(): void
    {
        $runtime = new Runtime();
        $binder  = new Binder();
        try {
            $runtime->configure($binder, $this->root->url());
            $injector = $binder->getInjector();
            assertThat($injector->getInstance(Environment::class), isInstanceOf(Production::class));
        } finally {
            restore_error_handler();
            restore_exception_handler();
        }
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function bindsEnvironmentProvidedViaCallable(): void
    {
        $runtime = new Runtime(function() { return $this->environment; });
        $binder  = new Binder();
        $runtime->configure($binder, $this->root->url());
        assertThat(
                $binder->getInjector()->getInstance(Environment::class),
                isSameAs($this->environment)
        );
        verify($this->environment, 'registerErrorHandler')
                ->received($this->root->url());
        verify($this->environment, 'registerExceptionHandler')
                ->received($this->root->url());
    }

    /**
     * @test
     * @since  4.0.0
     */
    public function createWithNonEnvironmentThrowsIllegalArgumentException(): void
    {
        expect(function() { new Runtime(new \stdClass()); })
            ->throws(\InvalidArgumentException::class);
    }

    /**
     * @test
     * @since  3.4.0
     */
    public function doesNotBindPropertiesWhenConfigFileIsMissing(): void
    {
        $binder = NewInstance::of(Binder::class);
        $runtime = new Runtime($this->environment);
        $runtime->configure($binder, $this->root->url());
        verify($binder, 'bindProperties')->wasNeverCalled();
    }

    /**
     * @test
     * @since  3.4.0
     */
    public function bindsPropertiesWhenConfigFilePresent(): void
    {
        vfsStream::newFile('config/config.ini')
                 ->withContent("[config]
stubbles.locale=\"de_DE\"
stubbles.number.decimals=4
stubbles.webapp.xml.serializeMode=true")
                 ->at($this->root);
        $binder  = NewInstance::of(Binder::class);
        $runtime = new Runtime($this->environment);
        $runtime->configure($binder, $this->root->url());
        verify($binder, 'bindProperties')->wasCalledOnce();
    }

    /**
     * @test
     */
    public function projectPathIsBound(): void
    {
        $binder  = new Binder();
        $runtime = new Runtime($this->environment);
        $runtime->configure($binder, $this->root->url());
        assertThat(
                $binder->getInjector()->getConstant('stubbles.project.path'),
                equals($this->root->url())
        );
    }

    /**
     * @return  array<string,string[]>
     */
    public function getConstants(): array
    {
        return ['config' => ['config', 'stubbles.config.path'],
                'log'    => ['log', 'stubbles.log.path']
        ];
    }

    private function getProjectPath(string $part): string
    {
        return $this->root->url() . DIRECTORY_SEPARATOR . $part;
    }

    /**
     * @param  string  $pathPart
     * @param  string  $constantName
     * @test
     * @dataProvider  getConstants
     */
    public function pathesShouldBeBoundAsConstant(string $pathPart, string $constantName): void
    {
        $binder  = new Binder();
        $runtime = new Runtime($this->environment);
        $runtime->configure($binder, $this->root->url());
        assertThat(
                $binder->getInjector()->getConstant($constantName),
                equals($this->getProjectPath($pathPart))
        );
    }

    /**
     * returns constant names and values
     *
     * @return  array<string,string[]>
     */
    public function getWithAdditionalConstants(): array
    {
        return array_merge(
                $this->getConstants(),
                ['user' => ['user', 'stubbles.user.path']]
        );
    }

    /**
     * @param  string  $pathPart
     * @param  string  $constantName
     * @test
     * @dataProvider  getWithAdditionalConstants
     */
    public function additionalPathTypesShouldBeBound(string $pathPart, string $constantName): void
    {
        $binder  = new Binder();
        $runtime = new Runtime($this->environment);
        $runtime->addPathType('user')->configure($binder, $this->root->url());
        assertThat(
                $binder->getInjector()->getConstant($constantName),
                equals($this->getProjectPath($pathPart))
        );
    }
}
