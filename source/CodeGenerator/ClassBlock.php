<?php

namespace CodeGenerator;

class ClassBlock extends Block
{
    /** @var string */
    private $name;

    /** @var string */
    private $namespace;

    /** @var string */
    private $parentClassName;

    /** @var string[] */
    private $interfaces;

    /** @var string[] */
    private $uses = [];

    /** @var ConstantBlock[] */
    private $constants = [];

    /** @var PropertyBlock[] */
    private $properties = [];

    /** @var MethodBlock[] */
    private $methods = [];

    /** @var bool */
    private $abstract;

    /**
     * @param string $name
     * @param string|null $parentClassName
     * @param array|null $interfaces
     */
    public function __construct($name, $parentClassName = null, array $interfaces = null)
    {
        $this->name = (string)$name;
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
        $this->parentClassName = (string)$parentClassName;
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
        $this->interfaces[] = $interface;
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
        $this->namespace = (string)$namespace;
    }

    /**
     * @param bool $abstract
     */
    public function setAbstract($abstract)
    {
        $this->abstract = (bool)$abstract;
    }

    /**
     * @param MethodBlock $method
     */
    public function addMethod(MethodBlock $method)
    {
        $this->methods[$method->getName()] = $method;
    }

    /**
     * @param PropertyBlock $property
     */
    public function addProperty(PropertyBlock $property)
    {
        $this->properties[$property->getName()] = $property;
    }

    /**
     * @param ConstantBlock $constant
     */
    public function addConstant(ConstantBlock $constant)
    {
        $this->constants[$constant->getName()] = $constant;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function addUse($name)
    {
        $this->uses[] = $name;
    }

    /**
     * @return string
     */
    protected function dumpContent()
    {
        $lines = [];
        $lines[] = $this->dumpHeader();
        foreach ($this->uses as $use) {
            $lines[] = $this->indent("use ${use};");
            $lines[] = '';
        }
        foreach ($this->constants as $constant) {
            $lines[] = $this->indent($constant->dump());
            $lines[] = '';
        }
        foreach ($this->properties as $property) {
            $lines[] = $this->indent($property->dump());
            $lines[] = '';
        }
        foreach ($this->methods as $method) {
            $lines[] = $this->indent($method->dump());
            $lines[] = '';
        }
        if (!empty($this->uses) || !empty($this->constants) || !empty($this->properties) || !empty($this->methods)) {
            array_pop($lines);
        }

        $lines[] = $this->dumpFooter();

        return $this->dumpLines($lines);
    }

    /**
     * @return string
     */
    private function dumpHeader()
    {
        $lines = [];
        if ($this->namespace) {
            $lines[] = 'namespace ' . $this->namespace . ';';
            $lines[] = '';
        }
        $classDeclaration = '';
        if ($this->abstract) {
            $classDeclaration .= self::KEYWORD_ABSTRACT . ' ';
        }
        $classDeclaration .= 'class ' . $this->name;
        if ($this->parentClassName) {
            $classDeclaration .= ' extends ' . $this->getParentClassName();
        }
        if ($this->interfaces) {
            $classDeclaration .= ' implements ' . implode(', ', $this->getInterfaces());
        }
        $lines[] = $classDeclaration;
        $lines[] = '{';

        return $this->dumpLines($lines);
    }

    /**
     * @return string
     */
    private function getParentClassName()
    {
        return self::normalizeClassName($this->parentClassName);
    }

    /**
     * @return string[]
     */
    private function getInterfaces()
    {
        return array_map(['\\CodeGenerator\\ClassBlock', 'normalizeClassName'], $this->interfaces);
    }

    /**
     * @return string
     */
    private function dumpFooter()
    {
        return '}';
    }
}
