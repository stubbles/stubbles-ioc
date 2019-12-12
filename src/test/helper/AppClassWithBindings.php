<?php
declare(strict_types=1);
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace stubbles\test;
use stubbles\App;
use stubbles\ioc\Binder;
use stubbles\ioc\Injector;
/**
 * Helper class for ioc tests.
 */
class AppClassWithBindings extends App
{
    /**
     * bound by value for retrieval
     *
     * @var  string
     */
    private $boundBy;

    public $injector;

    public $pathOfProject;

    /**
     * return list of bindings required for this command
     *
     * @return  array
     */
    public static function __bindings(): array
    {
        return [
                new AppTestBindingModuleOne(),
                new AppTestBindingModuleTwo(),
                function(Binder $binder)
                {
                    $binder->bindConstant('boundBy')->to('closure');
                }
        ];
    }

    /**
     *
     * @param  \stubbles\ioc\Injector  $injector
     * @param  string                  $projectPath
     * @param  string                  $boundBy      optional
     * @Named{projectPath}('stubbles.project.path')
     * @Named{boundBy}('boundBy')
     */
    public function __construct(Injector $injector, string $projectPath, string $boundBy = null)
    {
        $this->injector      = $injector;
        $this->pathOfProject = $projectPath;
        $this->boundBy       = $boundBy;
    }

    /**
     * returns value and how it was bound
     *
     * @return  string
     */
    public function wasBoundBy(): string
    {
        return $this->boundBy;
    }

    /**
     * runs the command
     */
    public function run(): void { }
}
