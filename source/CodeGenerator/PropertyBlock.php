<?php

namespace CodeGenerator;

class PropertyBlock extends Block
{
    /** @var string|null */
    protected $_docBlock;
    /** @var string */
    private $_name;
    /** @var string */
    private $_visibility;

    /** @var bool */
    private $_static;

    /** @var mixed */
    private $_defaultValue;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->_name = (string)$name;
        $this->setVisibility('public');
        $this->setStatic(false);
    }

    /**
     * @param string $visibility
     */
    public function setVisibility($visibility)
    {
        $this->_visibility = (string)$visibility;
    }

    /**
     * @param boolean $static
     */
    public function setStatic($static)
    {
        $this->_static = (bool)$static;
    }

    /**
     * @param \ReflectionProperty $reflection
     * @return PropertyBlock
     */
    public static function buildFromReflection(\ReflectionProperty $reflection)
    {
        $property = new self($reflection->getName());
        $property->extractFromReflection($reflection);

        // $property->setDefaultValue($reflection->getValue());
        return $property;
    }

    /**
     * @param \ReflectionProperty $reflection
     */
    public function extractFromReflection(\ReflectionProperty $reflection)
    {
        $this->_setStaticFromReflection($reflection);
        $this->_setVisibilityFromReflection($reflection);
        $this->_setDefaultValueFromReflection($reflection);
        $this->_setDocBlockFromReflection($reflection);
    }

    protected function _setStaticFromReflection(\ReflectionProperty $reflection)
    {
        if ($reflection->isStatic()) {
            $this->setStatic(true);
        }
    }

    /**
     * @param \ReflectionProperty $reflection
     */
    protected function _setVisibilityFromReflection(\ReflectionProperty $reflection)
    {
        if ($reflection->isPublic()) {
            $this->setVisibility('public');
        }
        if ($reflection->isProtected()) {
            $this->setVisibility('protected');
        }
        if ($reflection->isPrivate()) {
            $this->setVisibility('private');
        }
    }

    /**
     * @param \ReflectionProperty $reflection
     */
    protected function _setDefaultValueFromReflection(\ReflectionProperty $reflection)
    {
        $defaultProperties = $reflection->getDeclaringClass()->getDefaultProperties();
        $value = $defaultProperties[$this->getName()];
        if (null !== $value) {
            $this->setDefaultValue($value);
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param mixed $value
     */
    public function setDefaultValue($value)
    {
        $this->_defaultValue = $value;
    }

    protected function _setDocBlockFromReflection(\ReflectionProperty $reflection)
    {
        $docBlock = $reflection->getDocComment();
        if ($docBlock) {
            $docBlock = preg_replace('/([\n\r])(' . self::$_indentation . ')+/', '$1', $docBlock);
            $this->setDocBlock($docBlock);
        }
    }

    /**
     * @param string|null $docBlock
     */
    public function setDocBlock($docBlock)
    {
        if (null !== $docBlock) {
            $docBlock = (string)$docBlock;
        }
        $this->_docBlock = $docBlock;
    }

    public function dump()
    {
        return $this->_dumpLine(
            $this->_dumpDocBlock(),
            $this->_dumpValue()
        );
    }

    /**
     * @return string
     */
    protected function _dumpDocBlock()
    {
        return $this->_docBlock;
    }

    /**
     * @return string
     */
    protected function _dumpValue()
    {
        $content = $this->_visibility;
        $content .= ($this->_static) ? ' static' : '';
        $content .= ' $' . $this->_name;
        if (null !== $this->_defaultValue) {
            $value = new ValueBlock($this->_defaultValue);
            $content .= ' = ' . $value->dump();
        }
        $content .= ';';

        return $content;
    }
}
