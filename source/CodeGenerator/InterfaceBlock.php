<?php

namespace CodeGenerator;

class InterfaceBlock extends Block
{
    /** @var string */
    private $name;

    /** @var string */
    private $namespace;

    /** @var array */
    private $parentInterfaceNames = [];

    /** @var ConstantBlock[] */
    private $constants = [];

    /** @var MethodBlock[] */
    private $methods = [];

    /**
     * @param $name
     * @param array|null $parentInterfaceNames
     */
    public function __construct($name, array $parentInterfaceNames = null)
    {
        $this->name = (string)$name;

        if (!is_null($parentInterfaceNames)) {
            $this->setParentInterfaceNames($parentInterfaceNames);
        }
    }

    /**
     * @param array $parentInterfaceNames
     */
    public function setParentInterfaceNames(array $parentInterfaceNames)
    {
        foreach ($parentInterfaceNames as $parentInterfaceName) {
            $this->addParentInterfaceName($parentInterfaceName);
        }
    }

    /**
     * @param string $parentInterfaceName
     */
    public function addParentInterfaceName($parentInterfaceName)
    {
        $this->parentInterfaceNames[] = (string)$parentInterfaceName;
    }

    /**
     * @param \ReflectionClass $reflection
     *
     * @return InterfaceBlock
     */
    public static function buildFromReflection(\ReflectionClass $reflection)
    {
        $class = new self($reflection->getShortName());
        $class->setNamespace($reflection->getNamespaceName());
        $reflectionParentInterfaces = $reflection->getInterfaces();

        if (!empty($reflectionParentInterfaces)) {
            $interfaces = [];
            foreach ($reflectionParentInterfaces as $reflectionParentInterface) {
                $interfaces[] = $reflectionParentInterface->getName();
            }
            $class->setParentInterfaceNames($interfaces);
        }

        foreach ($reflection->getMethods() as $reflectionMethod) {
            if ($reflectionMethod->getDeclaringClass() == $reflection) {
                $method = InterfaceMethodBlock::buildFromReflection($reflectionMethod);
                $class->addMethod($method);
            }
        }

        $constants = $reflection->getConstants();
        if (count($constants)) {
            $parentConstants = self::getAllConstantsOfParentInterfaces($reflection);
            foreach ($constants as $name => $value) {
                if (!in_array($name, $parentConstants)) {
                    $class->addConstant(new ConstantBlock($name, $value));
                }
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
     * @param InterfaceMethodBlock $method
     */
    public function addMethod(InterfaceMethodBlock $method)
    {
        $this->methods[$method->getName()] = $method;
    }

    /**
     * @param \ReflectionClass $reflection
     *
     * @return array
     */
    protected static function getAllConstantsOfParentInterfaces(\ReflectionClass $reflection)
    {
        $parentConstants = [];
        $parentInterfaces = $reflection->getInterfaces();
        foreach ($parentInterfaces as $parentInterface) {
            $parentInterfaceConstants = $parentInterface->getConstants();
            $constantNames = array_keys($parentInterfaceConstants);
            $parentConstants += $constantNames;
        }

        return $parentConstants;
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
     * @return string
     */
    protected function dumpContent()
    {
        $lines = [];
        $lines[] = $this->dumpHeader();
        foreach ($this->constants as $constant) {
            $lines[] = $this->indent($constant->dump());
            $lines[] = '';
        }
        foreach ($this->methods as $method) {
            $lines[] = $this->indent($method->dump());
            $lines[] = '';
        }
        if (!empty($this->constants) || !empty($this->methods)) {
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

        $classDeclaration = 'interface ' . $this->name;
        if ($this->parentInterfaceNames) {
            $classDeclaration .= ' extends ' . $this->getParentInterfaces();
        }
        $lines[] = $classDeclaration;
        $lines[] = '{';

        return $this->dumpLines($lines);
    }

    /**
     * @return string
     */
    private function getParentInterfaces()
    {
        $cleaned = [];
        foreach ($this->parentInterfaceNames as $parentInterfaceName) {
            $cleaned[] = self::normalizeClassName($parentInterfaceName);
        }

        return implode(', ', $cleaned);
    }

    /**
     * @return string
     */
    private function dumpFooter()
    {
        return '}';
    }

    /**
     * @param $namespacedClassOrInterfaceName
     */
    public function extractConstantsFromOtherClassOrInterface($namespacedClassOrInterfaceName)
    {
        $reflection = new \ReflectionClass($namespacedClassOrInterfaceName);
        $constants = $reflection->getConstants();

        foreach ($constants as $name => $val) {
            $this->addConstant(new ConstantBlock($name, $val));
        }
    }
}
