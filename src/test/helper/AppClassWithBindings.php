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
     * return list of bindings required for this command
     *
     * @return  array
     */
    public static function __bindings(): array
    {
        return [
            new AppTestBindingModuleOne(),
            new AppTestBindingModuleTwo(),
            function(Binder $binder): void
            {
                $binder->bindConstant('boundBy')->to('closure');
            }
        ];
    }

    /**
     * @Named{pathOfProject}('stubbles.project.path')
     * @Named{boundBy}('boundBy')
     */
    public function __construct(
        public Injector $injector,
        public string $pathOfProject,
        private ?string $boundBy = null
    ) { }

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
