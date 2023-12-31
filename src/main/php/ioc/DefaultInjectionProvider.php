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
namespace stubbles\ioc;

use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use stubbles\ioc\binding\BindingException;
use stubbles\ioc\binding\ConstantBinding;
use stubbles\ioc\binding\ListBinding;
use stubbles\ioc\binding\MapBinding;
use stubbles\ioc\binding\PropertyBinding;

use function stubbles\reflect\annotationsOf;
/**
 * Default injection provider.
 *
 * Creates objects and injects all dependencies via the default injector.
 *
 * @internal
 * @implements InjectionProvider<object>
 * @template T of object
 */
class DefaultInjectionProvider implements InjectionProvider
{
    /**
     * constructor
     *
     * @param  Injector  $injector
     * @param  ReflectionClass<T>     $impl
     */
    public function __construct(private Injector $injector, private ReflectionClass $class) { }

    /**
     * returns the value to provide
     *
     * @return  T
     */
    public function get(string $name = null)
    {
        $constructor = $this->class->getConstructor();
        if (null === $constructor || $this->class->isInternal()) {
            return $this->class->newInstance();
        }

        $params = $this->injectionValuesForMethod($constructor);
        if (count($params) === 0) {
            return $this->class->newInstance();
        }

        return $this->class->newInstanceArgs($params);
    }

    /**
     * returns a list of all injection values for given method
     *
     * @return  array<int,mixed>
     * @throws  BindingException
     */
    private function injectionValuesForMethod(ReflectionMethod $method): array
    {
        $paramValues = [];
        $defaultName = $this->methodBindingName($method);
        foreach ($method->getParameters() as $param) {
            $type  = $this->paramType($method, $param);
            $name  = $this->detectBindingName($param, $defaultName);
            $hasExplicitBinding = $this->injector->hasExplicitBinding($type, $name);
            if (!$hasExplicitBinding && $param->isDefaultValueAvailable()) {
                $paramValues[] = $param->getDefaultValue();
                continue;
            }

            if (!$this->injector->hasBinding($type, $name)) {
                $typeMsg = $this->createTypeMessage($type, $name);
                throw new BindingException(
                    sprintf(
                        'Can not inject into %s::%s(%s). No binding for type %s specified. Injection stack:\n%s',
                        $this->class->getName(),
                        $method->getName(),
                        $this->createParamString($param, $type),
                        $typeMsg,
                        join("\n", $this->injector->stack())
                    )
                );
            }

            $paramValues[] = $this->injector->getInstance($type, $name);
        }

        return $paramValues;
    }

    /**
     * returns default binding name for all parameters on given method
     */
    private function methodBindingName(ReflectionMethod $method): ?string
    {
        $annotations = annotationsOf($method);
        if ($annotations->contain('List')) {
            return $annotations->firstNamed('List')->getValue();
        }

        if ($annotations->contain('Map')) {
            return $annotations->firstNamed('Map')->getValue();
        }

        if ($annotations->contain('Named')) {
            return $annotations->firstNamed('Named')->getName();
        }

        if ($annotations->contain('Property')) {
            return $annotations->firstNamed('Property')->getValue();
        }

        return null;
    }

    /**
     * returns type of param
     */
    private function paramType(ReflectionMethod $method, ReflectionParameter $param): string
    {
        $methodAnnotations = annotationsOf($method);
        $paramAnnotations  = annotationsOf($param);
        $paramClass        = $param->getType();
        if (
            null !== $paramClass
            && $paramClass instanceof ReflectionNamedType
        ) {
            if ($methodAnnotations->contain('Property') || $paramAnnotations->contain('Property')) {
                return PropertyBinding::TYPE;
            }

            if (!$paramClass->isBuiltin()) {
                return $paramClass->getName();
            }
        }

        if ($methodAnnotations->contain('List') || $paramAnnotations->contain('List')) {
            return ListBinding::TYPE;
        }

        if ($methodAnnotations->contain('Map') || $paramAnnotations->contain('Map')) {
            return MapBinding::TYPE;
        }

        if ($methodAnnotations->contain('Property') || $paramAnnotations->contain('Property')) {
            return PropertyBinding::TYPE;
        }

        return ConstantBinding::TYPE;
    }

    private function detectBindingName(
        ReflectionParameter $param,
        string $default = null
    ): string|ReflectionClass|null {
        $annotations = annotationsOf($param);
        if ($annotations->contain('List')) {
            return $annotations->firstNamed('List')->getValue();
        }

        if ($annotations->contain('Map')) {
            return $annotations->firstNamed('Map')->getValue();
        }

        if ($annotations->contain('Named')) {
            return $annotations->firstNamed('Named')->getName();
        }

        if ($annotations->contain('Property')) {
            return $annotations->firstNamed('Property')->getValue();
        }

        return $default;
    }

    /**
     * creates the complete type message
     *
     * @param   string                           $type  type to create message for
     * @param   string|\ReflectionClass<object>  $name  name of named parameter
     * @return  string
     */
    private function createTypeMessage(
        string $type, 
        string|ReflectionClass|null $name = null
    ): string {
        if (null === $name) {
            return $type;
        }

        if (is_string($name)) {
           return $type . ' (named "' . $name . '")';
        }

        return $type . ' (named "' . $name->getName() . '")';
    }

    /**
     * creates the called method message
     */
    private function createParamString(ReflectionParameter $parameter, string $type): string
    {
        $message = '';
        if (!in_array($type, [PropertyBinding::TYPE, ConstantBinding::TYPE, ListBinding::TYPE, MapBinding::TYPE])) {
            $message .= $type . ' ';
        } else {
            $type = $parameter->getType();
            if ($type instanceof ReflectionNamedType && $type->getName() === 'array') {
                $message .= 'array ';
            }
        }

        return $message . '$' . $parameter->getName();
    }
}
