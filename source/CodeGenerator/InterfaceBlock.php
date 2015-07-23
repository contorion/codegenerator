<?php

namespace CodeGenerator;

class InterfaceBlock extends Block
{
    /** @var string */
    private $_name;

    /** @var string */
    private $_namespace;

    /** @var array */
    private $_parentInterfaceNames = [];

    /** @var ConstantBlock[] */
    private $_constants = [];

    /** @var MethodBlock[] */
    private $_methods = [];

    /**
     * @param $name
     * @param array|null $parentInterfaceNames
     */
    public function __construct($name, array $parentInterfaceNames = null)
    {
        $this->_name = (string)$name;

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
        $this->_parentInterfaceNames[] = (string)$parentInterfaceName;
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
        $this->_namespace = (string)$namespace;
    }

    /**
     * @param InterfaceMethodBlock $method
     */
    public function addMethod(InterfaceMethodBlock $method)
    {
        $this->_methods[$method->getName()] = $method;
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
     * @return string
     */
    public function dump()
    {
        $lines = [];
        $lines[] = $this->_dumpHeader();
        foreach ($this->_constants as $constant) {
            $lines[] = $this->_indent($constant->dump());
            $lines[] = '';
        }
        foreach ($this->_methods as $method) {
            $lines[] = $this->_indent($method->dump());
            $lines[] = '';
        }
        if (!empty($this->_constants) || !empty($this->_methods)) {
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

        $classDeclaration = 'interface ' . $this->_name;
        if ($this->_parentInterfaceNames) {
            $classDeclaration .= ' extends ' . $this->_getParentInterfaces();
        }
        $lines[] = $classDeclaration;
        $lines[] = '{';

        return $this->_dumpLines($lines);
    }

    /**
     * @return string
     */
    private function _getParentInterfaces()
    {
        $cleaned = [];
        foreach ($this->_parentInterfaceNames as $parentInterfaceName) {
            $cleaned[] = self::_normalizeClassName($parentInterfaceName);
        }

        return implode(', ', $cleaned);
    }

    /**
     * @return string
     */
    private function _dumpFooter()
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
