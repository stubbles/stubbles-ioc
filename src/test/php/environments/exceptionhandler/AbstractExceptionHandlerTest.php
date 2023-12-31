<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\environments\exceptionhandler;

use bovigo\callmap\ClassProxy;
use bovigo\callmap\NewInstance;
use Error;
use Exception;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Throwable;

use function bovigo\assert\assertThat;
use function bovigo\assert\assertFalse;
use function bovigo\assert\assertTrue;
use function bovigo\assert\predicate\equals;
use function bovigo\callmap\verify;
/**
 * Tests for stubbles\environments\exceptionhandler\AbstractExceptionHandler.
 */
#[Group('environments')]
#[Group('environments_exceptionhandler')]
class AbstractExceptionHandlerTest extends TestCase
{
    private AbstractExceptionHandler&ClassProxy $exceptionHandler;
    private vfsStreamDirectory $root;

    protected function setUp(): void
    {
        $this->root             = vfsStream::setup();
        $this->exceptionHandler = NewInstance::of(AbstractExceptionHandler::class, [vfsStream::url('root')])
            ->returns(['createResponseBody' => ''])
            ->stub('header', 'writeBody');
    }

    /**
     * @return  array<\Throwable[]>
     */
    public static function throwables(): array
    {
        return [
                [new Exception('failure message')],
                [new Error('failure message')]
        ];
    }

    #[Test]
    #[DataProvider('throwables')]
    public function loggingDisabledDoesNotCreateLogfile(Throwable $throwable): void
    {
        $this->exceptionHandler->disableLogging()
                ->handleException($throwable);
        assertFalse(
                $this->root->hasChild(
                        'log/errors/' . date('Y') . '/' . date('m')
                        . '/exceptions-' . date('Y-m-d') . '.log'
                )
        );
    }

    #[Test]
    #[DataProvider('throwables')]
    public function loggingNotDisabledCreatesLogfile(\Throwable $throwable): void
    {
        $this->exceptionHandler->handleException($throwable);
        assertTrue(
                $this->root->hasChild(
                        'log/errors/' . date('Y') . '/' . date('m')
                        . '/exceptions-' . date('Y-m-d') . '.log'
                )
        );
    }

    #[Test]
    #[DataProvider('throwables')]
    public function loggingDisabledFillsResponseOnly(\Throwable $throwable): void
    {
        $this->exceptionHandler->disableLogging()
                ->handleException($throwable);
        verify($this->exceptionHandler, 'header')->wasCalledOnce();
        verify($this->exceptionHandler, 'createResponseBody')->wasCalledOnce();
        verify($this->exceptionHandler, 'writeBody')->wasCalledOnce();
    }

    #[Test]
    #[DataProvider('throwables')]
    public function handleExceptionLogsExceptionData(\Throwable $throwable): void
    {
        $_SERVER['REQUEST_URI'] = '/some/path?query=param';
        $_SERVER['HTTP_HOST']   = 'localhost';
        unset($_SERVER['HTTPS']);
        $_SERVER['SERVER_PORT'] = '8080';
        $this->exceptionHandler->handleException($throwable);
        $line = __LINE__ - 1;
        /** @var  \org\bovigo\vfs\vfsStreamFile */
        $logfile = $this->root->getChild(
            'log/errors/' . date('Y') . '/' . date('m')
            . '/exceptions-' . date('Y-m-d') . '.log'
        );
        assertThat(
            substr($logfile->getContent(), 19),
            equals(
                '|' . get_class($throwable) . '|failure message|'
                . __FILE__ . '|' . $throwable->getLine() . "|||||http://localhost:8080/some/path?query=param\n"
            )
        );

    }

    #[Test]
    #[DataProvider('throwables')]
    public function handleChainedExceptionLogsExceptionDataOfChainedAndCause(\Throwable $throwable): void
    {
        $_SERVER['REQUEST_URI'] = '/some/path?query=param';
        $_SERVER['HTTP_HOST']   = 'localhost';
        $_SERVER['HTTPS'] = 1;
        unset($_SERVER['SERVER_PORT']);
        $exception = new \Exception('chained exception', 303, $throwable);
        $line      = __LINE__ - 1;
        $this->exceptionHandler->handleException($exception);
        /** @var  \org\bovigo\vfs\vfsStreamFile */
        $logfile = $this->root->getChild(
            'log/errors/' . date('Y') . '/' . date('m')
            . '/exceptions-' . date('Y-m-d') . '.log'
        );
        assertThat(
            substr($logfile->getContent(), 19),
            equals(
                '|Exception|chained exception|'
                . __FILE__ . '|' . $line . '|' . get_class($throwable) . '|failure message|'
                . __FILE__ . '|' . $throwable->getLine() . "|https://localhost/some/path?query=param\n"
            )
        );
    }

    #[Test]
    #[DataProvider('throwables')]
    public function createsLogDirectoryWithDefaultPermissionsIfNotExists(\Throwable $throwable): void
    {
        $this->exceptionHandler->handleException($throwable);
        assertThat(
            $this->root->getChild(
                'log/errors/' . date('Y') . '/' . date('m')
            )->getPermissions(),
            equals(0700)
        );
    }

    #[Test]
    #[DataProvider('throwables')]
    public function createLogDirectoryWithChangedPermissionsIfNotExists(\Throwable $throwable): void
    {
        $this->exceptionHandler->setFilemode(0777)->handleException($throwable);
        assertThat(
            $this->root->getChild(
                'log/errors/' . date('Y') . '/' . date('m')
            )->getPermissions(),
            equals(0777)
        );
    }
}
