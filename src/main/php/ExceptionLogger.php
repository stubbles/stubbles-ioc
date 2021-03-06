<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles
 */
namespace stubbles;
/**
 * Can be used to log exceptions.
 *
 * @since  3.3.0
 * @Singleton
 */
class ExceptionLogger
{
    /**
     * directory to log errors into
     *
     * @var  string
     */
    private $logDir;
    /**
     * mode for new directories
     *
     * @var  int
     */
    private $filemode = 0700;

    /**
     * constructor
     *
     * @param  string  $projectPath  path to project
     * @Named('stubbles.project.path')
     */
    public function __construct(string $projectPath)
    {
        $this->logDir = $projectPath
                . DIRECTORY_SEPARATOR
                . 'log'
                . DIRECTORY_SEPARATOR
                . 'errors'
                . DIRECTORY_SEPARATOR
                . '{Y}'
                . DIRECTORY_SEPARATOR
                . '{M}';
    }

    /**
     * sets the mode for new log directories
     *
     * @param   int  $filemode
     * @return  \stubbles\ExceptionLogger
     */
    public function setFilemode(int $filemode): self
    {
        $this->filemode = $filemode;
        return $this;
    }

    /**
     * logs the exception into a logfile
     *
     * @param  \Throwable  $throwable  exception to log
     * @param  string      $requestId  optional
     */
    public function log(\Throwable $throwable, string $requestId = ''): void
    {
        $logData  = date('Y-m-d H:i:s');
        $logData .= $this->fieldsOf($throwable);
        $logData .= $this->fieldsOf($throwable->getPrevious());
        $logData .= '|' . $requestId;
        error_log(
                $logData . "\n",
                3,
                $this->logDir() . DIRECTORY_SEPARATOR . 'exceptions-' . date('Y-m-d') . '.log'
        );
    }

    /**
     * returns fields for exception to log
     *
     * @param   \Throwable  $throwable  optional
     * @return  string
     */
    private function fieldsOf(\Throwable $throwable = null): string
    {
        if (null === $throwable) {
            return '||||';
        }

        return '|' . get_class($throwable)
             . '|' . $throwable->getMessage()
             . '|' . $throwable->getFile()
             . '|' . $throwable->getLine();
    }

    /**
     * returns directory where to write logfile to
     *
     * @return  string
     */
    private function logDir(): string
    {
        $logDir = str_replace(
                '{Y}',
                date('Y'),
                str_replace('{M}', date('m'), $this->logDir)
        );
        if (!file_exists($logDir)) {
            mkdir($logDir, $this->filemode, true);
        }

        return $logDir;
    }
}
