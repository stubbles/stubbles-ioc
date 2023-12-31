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
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

use function bovigo\assert\assertThat;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\equals;
use function bovigo\assert\predicate\isInstanceOf;
use function bovigo\assert\predicate\isSameAs;
use function bovigo\callmap\verify;
/**
 * Test for stubbles\ioc\module\Runtime.
 */
#[Group('app')]
class RuntimeTest extends TestCase
{
    private Environment&\bovigo\callmap\ClassProxy $environment;
    private vfsStreamDirectory $root;

    protected function setUp(): void
    {
        $this->root        = vfsStream::setup('projects');
        $this->environment = NewInstance::of(Environment::class)
            ->returns([
                'name' => 'TEST',
                'registerErrorHandler' => false,
                'registerExceptionHandler' => false,
            ]);
        Runtime::reset();
    }

    protected function tearDown(): void
    {
        Runtime::reset();
    }

    /**
     * @since  5.0.0
     */
    #[Test]
    public function runtimeIsNotInitializedWhenNoInstanceCreated(): void
    {
        assertFalse(Runtime::initialized());
    }

    /**
     * @since  5.0.0
     */
    #[Test]
    public function runtimeIsInitializedAfterFirstInstanceCreation(): void
    {
        new Runtime();
        assertTrue(Runtime::initialized());
    }

    #[Test]
    public function registerMethodsShouldBeCalledWithGivenProjectPath(): void
    {
        $runtime = new Runtime($this->environment);
        $runtime->configure(new Binder(), $this->root->url());
        verify($this->environment, 'registerErrorHandler')
            ->received($this->root->url());
        verify($this->environment, 'registerExceptionHandler')
            ->received($this->root->url());
    }

    #[Test]
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

    #[Test]
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
     * @since  4.0.0
     */
    #[Test]
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
     * @since  3.4.0
     */
    #[Test]
    public function doesNotBindPropertiesWhenConfigFileIsMissing(): void
    {
        $binder = NewInstance::of(Binder::class);
        $runtime = new Runtime($this->environment);
        $runtime->configure($binder, $this->root->url());
        verify($binder, 'bindProperties')->wasNeverCalled();
    }

    /**
     * @since  3.4.0
     */
    #[Test]
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

    #[Test]
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
    public static function getConstants(): array
    {
        return [
            'config' => ['config', 'stubbles.config.path'],
            'log'    => ['log', 'stubbles.log.path']
        ];
    }

    private function getProjectPath(string $part): string
    {
        return $this->root->url() . DIRECTORY_SEPARATOR . $part;
    }

    #[Test]
    #[DataProvider('getConstants')]
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
    public static function getWithAdditionalConstants(): array
    {
        return array_merge(
            self::getConstants(),
            ['user' => ['user', 'stubbles.user.path']]
        );
    }

    #[Test]
    #[DataProvider('getWithAdditionalConstants')]
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
