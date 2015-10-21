<?php

namespace CodeGenerator;

class ClassBlock extends Block
{
    /** @var string */
    private $_name;

    /** @var string */
    private $_namespace;

    /** @var string */
    private $_parentClassName;

    /** @var string[] */
    private $_interfaces;

    /** @var string[] */
    private $_uses = [];

    /** @var ConstantBlock[] */
    private $_constants = [];

    /** @var PropertyBlock[] */
    private $_properties = [];

    /** @var MethodBlock[] */
    private $_methods = [];

    /** @var bool */
    private $_abstract;

    /**
     * @param string $name
     * @param string|null $parentClassName
     * @param array|null $interfaces
     */
    public function __construct($name, $parentClassName = null, array $interfaces = null)
    {
        $this->_name = (string)$name;
        if (null !== $parentClassName) {
            $this->setParentClassName($parentClassName);
        }
        if (null !== $interfaces) {
            $this->setInterfaces($interfaces);
        }
    }

    /**
     * @param string $parentClassName
     */
    public function setParentClassName($parentClassName)
    {
        $this->_parentClassName = (string)$parentClassName;
    }

    /**
     * @param string[] $interfaces
     */
    public function setInterfaces(array $interfaces)
    {
        foreach ($interfaces as $interface) {
            $this->addInterface($interface);
        }
    }

    /**
     * @param string $interface
     */
    public function addInterface($interface)
    {
        $this->_interfaces[] = $interface;
    }

    public static function buildFromReflection(\ReflectionClass $reflection)
    {
        $class = new self($reflection->getShortName());
        $class->setNamespace($reflection->getNamespaceName());
        $reflectionParentClass = $reflection->getParentClass();
        if ($reflectionParentClass) {
            $class->setParentClassName($reflectionParentClass->getName());
        }
        $class->setAbstract($reflection->isAbstract());
        if ($interfaces = $reflection->getInterfaceNames()) {
            if ($reflectionParentClass) {
                $parentInterfaces = $reflection->getParentClass()->getInterfaceNames();
                $interfaces = array_diff($interfaces, $parentInterfaces);
            }
            $class->setInterfaces($interfaces);
        }
        foreach ($reflection->getMethods() as $reflectionMethod) {
            if ($reflectionMethod->getDeclaringClass() == $reflection) {
                $method = MethodBlock::buildFromReflection($reflectionMethod);
                $class->addMethod($method);
            }
        }
        foreach ($reflection->getProperties() as $reflectionProperty) {
            if ($reflectionProperty->getDeclaringClass() == $reflection) {
                $property = PropertyBlock::buildFromReflection($reflectionProperty);
                $class->addProperty($property);
            }
        }
        foreach ($reflection->getConstants() as $name => $value) {
            if (!$reflection->getParentClass() || ($reflection->getParentClass() && !$reflection->getParentClass()->hasConstant($name))) {
                $class->addConstant(new ConstantBlock($name, $value));
            }
        }

        return $class;
    }

    /**
     * @param string $namespace
     */
    public function setNamespace($namespace)
    {
        $this->_namespace = (string)$namespace;
    }

    /**
     * @param bool $abstract
     */
    public function setAbstract($abstract)
    {
        $this->_abstract = (bool)$abstract;
    }

    /**
     * @param MethodBlock $method
     */
    public function addMethod(MethodBlock $method)
    {
        $this->_methods[$method->getName()] = $method;
    }

    /**
     * @param PropertyBlock $property
     */
    public function addProperty(PropertyBlock $property)
    {
        $this->_properties[$property->getName()] = $property;
    }

    /**
     * @param ConstantBlock $constant
     */
    public function addConstant(ConstantBlock $constant)
    {
        $this->_constants[$constant->getName()] = $constant;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param string $name
     */
    public function addUse($name)
    {
        $this->_uses[] = $name;
    }

    /**
     * @return string
     */
    protected function dumpContent()
    {
        $lines = [];
        $lines[] = $this->_dumpHeader();
        foreach ($this->_uses as $use) {
            $lines[] = $this->_indent("use ${use};");
            $lines[] = '';
        }
        foreach ($this->_constants as $constant) {
            $lines[] = $this->_indent($constant->dump());
            $lines[] = '';
        }
        foreach ($this->_properties as $property) {
            $lines[] = $this->_indent($property->dump());
            $lines[] = '';
        }
        foreach ($this->_methods as $method) {
            $lines[] = $this->_indent($method->dump());
            $lines[] = '';
        }
        if (!empty($this->_uses) || !empty($this->_constants) || !empty($this->_properties) || !empty($this->_methods)) {
            array_pop($lines);
        }

        $lines[] = $this->_dumpFooter();

        return $this->_dumpLines($lines);
    }

    /**
     * @return string
     */
    private function _dumpHeader()
    {
        $lines = [];
        if ($this->_namespace) {
            $lines[] = 'namespace ' . $this->_namespace . ';';
            $lines[] = '';
        }
        $classDeclaration = '';
        if ($this->_abstract) {
            $classDeclaration .= 'abstract ';
        }
        $classDeclaration .= 'class ' . $this->_name;
        if ($this->_parentClassName) {
            $classDeclaration .= ' extends ' . $this->_getParentClassName();
        }
        if ($this->_interfaces) {
            $classDeclaration .= ' implements ' . implode(', ', $this->_getInterfaces());
        }
        $lines[] = $classDeclaration;
        $lines[] = '{';

        return $this->_dumpLines($lines);
    }

    /**
     * @return string
     */
    private function _getParentClassName()
    {
        return self::_normalizeClassName($this->_parentClassName);
    }

    /**
     * @return string[]
     */
    private function _getInterfaces()
    {
        return array_map(['\\CodeGenerator\\ClassBlock', '_normalizeClassName'], $this->_interfaces);
    }

    /**
     * @return string
     */
    private function _dumpFooter()
    {
        return '}';
    }
}
