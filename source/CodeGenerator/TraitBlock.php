<?php

namespace CodeGenerator;

class TraitBlock extends Block
{
    /** @var string */
    private $name;

    /** @var string */
    private $namespace;

    /** @var string[] */
    private $uses = [];

    /** @var PropertyBlock[] */
    private $properties = [];

    /** @var MethodBlock[] */
    private $methods = [];

    /**
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = (string)$name;
    }

    /**
     * @param \ReflectionClass $reflection
     *
     * @return TraitBlock
     */
    public static function buildFromReflection(\ReflectionClass $reflection)
    {
        $class = new self($reflection->getShortName());
        $class->setNamespace($reflection->getNamespaceName());

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
        foreach ($this->properties as $property) {
            $lines[] = $this->indent($property->dump());
            $lines[] = '';
        }
        foreach ($this->methods as $method) {
            $lines[] = $this->indent($method->dump());
            $lines[] = '';
        }
        if (!empty($this->uses) || !empty($this->properties) || !empty($this->methods)) {
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
        $classDeclaration = 'trait ' . $this->name;

        $lines[] = $classDeclaration;
        $lines[] = '{';

        return $this->dumpLines($lines);
    }

    /**
     * @return string
     */
    private function dumpFooter()
    {
        return '}';
    }
}
