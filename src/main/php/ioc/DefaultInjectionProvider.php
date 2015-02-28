<?php
/**
 * This file is part of stubbles.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package  stubbles
 */
namespace stubbles\ioc;
use stubbles\ioc\binding\BindingException;
use stubbles\ioc\binding\ConstantBinding;
use stubbles\ioc\binding\ListBinding;
use stubbles\ioc\binding\MapBinding;
use stubbles\ioc\binding\PropertyBinding;
use stubbles\lang\reflect;
use stubbles\lang\reflect\BaseReflectionClass;
use stubbles\lang\reflect\ReflectionMethod;
use stubbles\lang\reflect\ReflectionParameter;
/**
 * Default injection provider.
 *
 * Creates objects and injects all dependencies via the default injector.
 *
 * @internal
 */
class DefaultInjectionProvider implements InjectionProvider
{
    /**
     * injector to use for dependencies
     *
     * @type  \stubbles\ioc\Injector
     */
    private $injector;
    /**
     * concrete implementation to use
     *
     * @type  \stubbles\lang\reflect\BaseReflectionClass
     */
    private $class;

    /**
     * constructor
     *
     * @param  \stubbles\ioc\Injector                      $injector
     * @param  \stubbles\lang\reflect\BaseReflectionClass  $impl
     */
    public function __construct(Injector $injector, BaseReflectionClass $impl)
    {
        $this->injector = $injector;
        $this->class    = $impl;
    }

    /**
     * returns the value to provide
     *
     * @param   string  $name
     * @return  mixed
     */
    public function get($name = null)
    {
        $instance = $this->createInstance();
        if (!$this->class->isInternal() && Binder::isSetterInjectionEnabled()) {
            $this->injectIntoSetters($instance);
        }

        return $instance;
    }

    /**
     * creates instance
     *
     * @return  mixed
     */
    private function createInstance()
    {
        $constructor = $this->class->getConstructor();
        if (null === $constructor || $this->class->isInternal() || !reflect\annotationsOf($constructor)->contain('Inject')) {
            return $this->class->newInstance();
        }

        $params = $this->injectionValuesForMethod($constructor);
        if (false === $params && reflect\annotationsOf($constructor)->named('Inject')[0]->isOptional()) {
            return $this->class->newInstance();
        }

        return $this->class->newInstanceArgs($params);
    }

    /**
     * handle injections for given instance
     *
     * @param  object  $instance
     */
    private function injectIntoSetters($instance)
    {
        foreach ($this->class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            /* @type  $method  ReflectionMethod */
            if ($method->isStatic()
              || $method->getNumberOfParameters() === 0
              || strncmp($method->getName(), '__', 2) === 0
              || !reflect\annotationsOf($method)->contain('Inject')) {
                continue;
            }

            $paramValues = $this->injectionValuesForMethod($method);
            if (false === $paramValues) {
                continue;
            }

            $method->invokeArgs($instance, $paramValues);
        }
    }

    /**
     * returns a list of all injection values for given method
     *
     * @param   \stubbles\lang\reflect\ReflectionMethod  $method
     * @return  array
     * @throws  \stubbles\ioc\binding\BindingException
     */
    private function injectionValuesForMethod(ReflectionMethod $method)
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
            } elseif (!$hasExplicitBinding && reflect\annotationsOf($method)->named('Inject')[0]->isOptional()) {
                return false;
            }

            if (!$this->injector->hasBinding($type, $name)) {
                $typeMsg = $this->createTypeMessage($type, $name);
                throw new BindingException(
                        'Can not inject into ' . $this->class->getName() . '::' . $method->getName() . '('
                        . $this->createParamString($param, $type)
                        . '). No binding for type ' . $typeMsg
                        . ' specified. Injection stack: ' . "\n"
                        . join("\n", $this->injector->stack())
                );
            }

            $paramValues[] = $this->injector->getInstance($type, $name);
        }

        return $paramValues;
    }

    /**
     * returns default binding name for all parameters on given method
     *
     * @param   \stubbles\lang\reflect\ReflectionMethod  $method
     * @return  string
     */
    private function methodBindingName(ReflectionMethod $method)
    {
        $annotations = reflect\annotationsOf($method);
        if ($annotations->contain('List')) {
            return $annotations->named('List')[0]->getValue();
        }

        if ($annotations->contain('Map')) {
            return $annotations->named('Map')[0]->getValue();
        }

        if ($annotations->contain('Named')) {
            return $annotations->named('Named')[0]->getName();
        }

        if ($annotations->contain('Property')) {
            return $annotations->named('Property')[0]->getValue();
        }

        return null;
    }

    /**
     * returns type of param
     *
     * @param   \stubbles\lang\reflect\ReflectionMethod     $method
     * @param   \stubbles\lang\reflect\ReflectionParameter  $param
     * @return  string
     */
    private function paramType(ReflectionMethod $method, ReflectionParameter $param)
    {
        $methodAnnotations = reflect\annotationsOf($method);
        $paramAnnotations  = reflect\annotationsOf($param);
        $paramClass        = $param->getClass();
        if (null !== $paramClass) {
            if ($methodAnnotations->contain('Property') || $paramAnnotations->contain('Property')) {
                return PropertyBinding::TYPE;
            }

            return $paramClass->getName();
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

    /**
     * detects name for binding
     *
     * @param   \stubbles\lang\reflect\ReflectionParameter  $param
     * @param   string               $default
     * @return  string|\stubbles\lang\reflect\ReflectionClass
     */
    private function detectBindingName(ReflectionParameter $param, $default)
    {
        $annotations = reflect\annotationsOf($param);
        if ($annotations->contain('List')) {
            return $annotations->named('List')[0]->getValue();
        }

        if ($annotations->contain('Map')) {
            return $annotations->named('Map')[0]->getValue();
        }

        if ($annotations->contain('Named')) {
            return $annotations->named('Named')[0]->getName();
        }

        if ($annotations->contain('Property')) {
            return $annotations->named('Property')[0]->getValue();
        }

        return $default;
    }

    /**
     * creates the complete type message
     *
     * @param   string  $type  type to create message for
     * @param   string  $name  name of named parameter
     * @return  string
     */
    private function createTypeMessage($type, $name)
    {
        return ((null !== $name) ? ($type . ' (named "' . $name . '")') : ($type));
    }

    /**
     * creates the called method message
     *
     * @param   \stubbles\lang\reflect\ReflectionParameter  $parameter
     * @param   string                                      $type
     * @return  string
     */
    private function createParamString(ReflectionParameter $parameter, $type)
    {
        $message = '';
        if (!in_array($type, [PropertyBinding::TYPE, ConstantBinding::TYPE, ListBinding::TYPE, MapBinding::TYPE])) {
            $message .= $type . ' ';
        } elseif ($parameter->isArray()) {
            $message .= 'array ';
        }

        return $message . '$' . $parameter->getName();
    }
}
