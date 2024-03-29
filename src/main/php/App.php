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
use stubbles\ioc\Binder;
use stubbles\ioc\module\BindingModule;
/**
 * Application base class.
 */
abstract class App
{
    /**
     * runs the application
     *
     * @api
     * @since  2.0.0
     */
    abstract public function run();

    /**
     * creates an object via injection
     *
     * If the class to create an instance of contains a static __bindings() method
     * this method will be used to configure the ioc bindings before using the ioc
     * container to create the instance.
     *
     * @api
     */
    public static function create(string $projectPath): static
    {
        return self::createInstance(get_called_class(), $projectPath);
    }

    /**
     * project path, set when instance is created
     */
    private static string $projectPath;

    /**
     * returns current project path
     *
     * Should only be used during app initialization, but never at runtime.
     *
     * @since  5.0.0
     */
    protected static function projectPath(): string
    {
        return self::$projectPath;
    }

    /**
     * creates an object via injection
     *
     * If the class to create an instance of contains a static __bindings() method
     * this method will be used to configure the ioc bindings before using the ioc
     * container to create the instance.
     *
     * @api
     * @template T of App
     * @param class-string<T> $className
     * @return T
     */
    public static function createInstance(string $className, string $projectPath): App
    {
        Runtime::reset();
        self::$projectPath = $projectPath;
        $binder = new Binder();
        foreach (static::getBindingsForApp($className) as $bindingModule) {
            if (is_string($bindingModule)) {
                $bindingModule = new $bindingModule();
            }

            if ($bindingModule instanceof BindingModule) {
                $bindingModule->configure($binder, $projectPath);
            } elseif ($bindingModule instanceof \Closure) {
                $bindingModule($binder, $projectPath);
            } else {
                throw new \InvalidArgumentException(
                        'Given module class ' . get_class($bindingModule)
                        . ' is not an instance of stubbles\ioc\module\BindingModule'
                );
            }
        }

        return $binder->getInjector()->getInstance($className);
    }

    /**
     * creates list of bindings from given class
     *
     * @internal  must not be used by applications
     * @return  array<string|BindingModule[]>
     * @since   1.3.0
     */
    protected static function getBindingsForApp(string $className): array
    {
        $bindings = method_exists($className, '__bindings') ? $className::__bindings() : [];
        if (!Runtime::initialized()) {
            $bindings[] = static::runtime();
        }

        return $bindings;
    }

    /**
     * creates mode binding module
     *
     * @api
     * @since  2.0.0
     */
    protected static function runtime(Environment|callable|null $environment = null): Runtime
    {
        return new Runtime($environment);
    }

    /**
     * create a binding module which binds current working directory
     *
     * @api
     */
    protected static function currentWorkingDirectory(): \Closure
    {
        return function(Binder $binder)
        {
            $binder->bindConstant('stubbles.cwd')
                   ->to(getcwd());
        };
    }

    /**
     * create a binding module which binds current hostnames
     *
     * @api
     */
    protected static function hostname(): \Closure
    {
        return function(Binder $binder)
        {
            if (DIRECTORY_SEPARATOR === '\\') {
                $fq = php_uname('n');
                if (isset($_SERVER['USERDNSDOMAIN'])) {
                    $fq .= '.' . $_SERVER['USERDNSDOMAIN'];
                }
            } else {
                $fq = exec('hostname -f');
            }

            $binder->bindConstant('stubbles.hostname.nq')
                   ->to(php_uname('n'));
            $binder->bindConstant('stubbles.hostname.fq')
                   ->to($fq);
        };
    }
}
