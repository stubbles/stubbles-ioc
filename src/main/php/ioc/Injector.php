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
use stubbles\ioc\binding\BindingIndex;
use stubbles\lang\reflect\BaseReflectionClass;
use stubbles\lang\reflect\ReflectionMethod;
use stubbles\lang\reflect\ReflectionObject;
use stubbles\lang\reflect\ReflectionParameter;
/**
 * Injector for the IoC functionality.
 *
 * Used to create the instances.
 */
class Injector
{
    /**
     * index for faster access to bindings
     *
     * @type  \stubbles\ioc\binding\BindingIndex
     */
    private $bindingIndex;

    /**
     * constructor
     *
     * @param  \stubbles\ioc\binding\BindingIndex   $bindingIndex
     * @since  1.5.0
     */
    public function __construct(BindingIndex $bindingIndex)
    {
        $this->bindingIndex = $bindingIndex;
    }

    /**
     * check whether a binding for a type is available (explicit and implicit)
     *
     * @api
     * @param   string   $type
     * @param   string   $name
     * @return  boolean
     */
    public function hasBinding($type, $name = null)
    {
        return $this->bindingIndex->hasBinding($type, $this->getBindingName($name));
    }

    /**
     * check whether an excplicit binding for a type is available
     *
     * Be aware that implicit bindings turn into explicit bindings when
     * hasBinding() or getInstance() are called.
     *
     * @api
     * @param   string   $type
     * @param   string   $name
     * @return  boolean
     */
    public function hasExplicitBinding($type, $name = null)
    {
        return $this->bindingIndex->hasExplicitBinding($type, $this->getBindingName($name));
    }

    /**
     * get an instance
     *
     * @api
     * @param   string  $type
     * @param   string  $name
     * @return  object
     */
    public function getInstance($type, $name = null)
    {
        return $this->bindingIndex->getBinding($type, $this->getBindingName($name))
                                  ->getInstance($this, $name);
    }

    /**
     * parses binding name from given name
     *
     * @param   string|\stubbles\lang\reflect\BaseReflectionClass  $name
     * @return  string
     */
    private function getBindingName($name)
    {
        if ($name instanceof BaseReflectionClass) {
            return $name->getName();
        }

        return $name;
    }

    /**
     * check whether a constant is available
     *
     * @api
     * @param   string  $name  name of constant to check for
     * @return  bool
     * @since   1.1.0
     */
    public function hasConstant($name)
    {
        return $this->bindingIndex->hasConstant($name);
    }

    /**
     * returns constanct value
     *
     * @api
     * @param   string  $name  name of constant value to retrieve
     * @return  scalar
     * @since   1.1.0
     */
    public function getConstant($name)
    {
        return $this->bindingIndex->getConstantBinding($name)
                                  ->getInstance($this, $name);
    }

    /**
     * checks whether list binding for given name exists
     *
     * @param   string  $name
     * @return  bool
     */
    public function hasList($name)
    {
        return $this->bindingIndex->hasList($name);
    }

    /**
     * returns list for given name
     *
     * @param   string  $name
     * @return  array
     */
    public function getList($name)
    {
        return $this->bindingIndex->getListBinding($name)
                                  ->getInstance($this, $name);
    }

    /**
     * checks whether map binding for given name exists
     *
     * @param   string  $name
     * @return  bool
     */
    public function hasMap($name)
    {
        return $this->bindingIndex->hasMap($name);
    }

    /**
     * returns map for given name
     *
     * @param   string  $name
     * @return  array
     */
    public function getMap($name)
    {
        return $this->bindingIndex->getMapBinding($name)
                                  ->getInstance($this, $name);
    }

    /**
     * handle injections for given instance
     *
     * @param   object                                      $instance
     * @param   \stubbles\lang\reflect\BaseReflectionClass  $class
     */
    public function handleInjections($instance, BaseReflectionClass $class = null)
    {
        if (null === $class) {
            $class = new ReflectionObject($instance);
        }

        foreach ($class->getMethods() as $method) {
            /* @type  $method  ReflectionMethod */
            if (!$method->isPublic()
              || $method->isStatic()
              || $method->getNumberOfParameters() === 0
              || strncmp($method->getName(), '__', 2) === 0
              || !$method->hasAnnotation('Inject')) {
                continue;
            }

            $paramValues = $this->getInjectionValuesForMethod($method, $class);
            if (false === $paramValues) {
                continue;
            }

            $method->invokeArgs($instance, $paramValues);
        }
    }

    /**
     * returns a list of all injection values for given method
     *
     * @param   \stubbles\lang\reflect\ReflectionMethod     $method
     * @param   \stubbles\lang\reflect\BaseReflectionClass  $class
     * @return  array
     * @throws  \stubbles\ioc\binding\BindingException
     */
    public function getInjectionValuesForMethod(ReflectionMethod $method, BaseReflectionClass $class)
    {
        $paramValues = [];
        $defaultName = $this->getMethodBindingName($method);
        foreach ($method->getParameters() as $param) {
            $type  = $this->getParamType($method, $param);
            $name  = $this->detectBindingName($param, $defaultName);
            if (!$this->hasExplicitBinding($type, $name) && $method->annotation('Inject')->isOptional()) {
                return false;
            }

            if (!$this->hasBinding($type, $name)) {
                $typeMsg = $this->createTypeMessage($type, $name);
                throw new BindingException('Can not inject into ' . $this->createCalledMethodMessage($class, $method, $param, $type)  . '. No binding for type ' . $typeMsg . ' specified.');
            }

            $paramValues[] = $this->getInstance($type, $name);
        }

        return $paramValues;
    }

    /**
     * returns default binding name for all parameters on given method
     *
     * @param   \stubbles\lang\reflect\ReflectionMethod  $method
     * @return  string
     */
    private function getMethodBindingName(ReflectionMethod $method)
    {
        if ($method->hasAnnotation('List')) {
            return $method->annotation('List')->getValue();
        }

        if ($method->hasAnnotation('Map')) {
            return $method->annotation('Map')->getValue();
        }

        if ($method->hasAnnotation('Named')) {
            return $method->annotation('Named')->getName();
        }

        if ($method->hasAnnotation('Property')) {
            return $method->annotation('Property')->getValue();
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
    private function getParamType(ReflectionMethod $method, ReflectionParameter $param)
    {
        $paramClass = $param->getClass();
        if (null !== $paramClass) {
            if ($method->hasAnnotation('Property') || $param->hasAnnotation('Property')) {
                return BindingIndex::getPropertyType();
            }

            return $paramClass->getName();
        }

        if ($method->hasAnnotation('List') || $param->hasAnnotation('List')) {
            return BindingIndex::getListType();
        }

        if ($method->hasAnnotation('Map') || $param->hasAnnotation('Map')) {
            return BindingIndex::getMapType();
        }

        if ($method->hasAnnotation('Property') || $param->hasAnnotation('Property')) {
            return BindingIndex::getPropertyType();
        }

        return BindingIndex::getConstantType();
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
        if ($param->hasAnnotation('List')) {
            return $param->annotation('List')->getValue();
        }

        if ($param->hasAnnotation('Map')) {
            return $param->annotation('Map')->getValue();
        }

        if ($param->hasAnnotation('Named')) {
            return $param->annotation('Named')->getName();
        }

        if ($param->hasAnnotation('Property')) {
            return $param->annotation('Property')->getValue();
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
     * @param   \stubbles\lang\reflect\BaseReflectionClass  $class
     * @param   \stubbles\lang\reflect\ReflectionMethod     $method
     * @param   \stubbles\lang\reflect\ReflectionParameter  $parameter
     * @param   string                                      $type
     * @return  string
     */
    private function createCalledMethodMessage(BaseReflectionClass $class, ReflectionMethod $method, ReflectionParameter $parameter, $type)
    {
        $message = $class->getName() . '::' . $method->getName() . '(';
        if ($this->bindingIndex->isObjectBinding($type)) {
            $message .= $type . ' ';
        } elseif ($parameter->isArray()) {
            $message .= 'array ';
        }

        return $message . '$' . $parameter->getName() . ')';
    }
}
