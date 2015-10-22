<?php

namespace CodeGenerator;

class PropertyBlock extends Block
{
    /** @var string */
    private $name;
    /** @var string */
    private $visibility;

    /** @var bool */
    private $static;

    /** @var mixed */
    private $defaultValue;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = (string)$name;
        $this->setVisibility(self::VISIBILITY_PUBLIC);
        $this->setStatic(false);
    }

    /**
     * @param string $visibility
     */
    public function setVisibility($visibility)
    {
        $this->visibility = (string)$visibility;
    }

    /**
     * @param boolean $static
     */
    public function setStatic($static)
    {
        $this->static = (bool)$static;
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
        $this->setStaticFromReflection($reflection);
        $this->setVisibilityFromReflection($reflection);
        $this->setDefaultValueFromReflection($reflection);
        $this->setDocBlockFromReflection($reflection);
    }

    protected function setStaticFromReflection(\ReflectionProperty $reflection)
    {
        if ($reflection->isStatic()) {
            $this->setStatic(true);
        }
    }

    /**
     * @param \ReflectionProperty $reflection
     */
    protected function setVisibilityFromReflection(\ReflectionProperty $reflection)
    {
        if ($reflection->isPublic()) {
            $this->setVisibility(self::VISIBILITY_PUBLIC);
        }
        if ($reflection->isProtected()) {
            $this->setVisibility(self::VISIBILITY_PROTECTED);
        }
        if ($reflection->isPrivate()) {
            $this->setVisibility(self::VISIBILITY_PRIVATE);
        }
    }

    /**
     * @param \ReflectionProperty $reflection
     */
    protected function setDefaultValueFromReflection(\ReflectionProperty $reflection)
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
        return $this->name;
    }

    /**
     * @param mixed $value
     */
    public function setDefaultValue($value)
    {
        $this->defaultValue = $value;
    }

    protected function dumpContent()
    {
        return $this->dumpLine(
            $this->_dumpValue()
        );
    }

    /**
     * @return string
     */
    protected function _dumpValue()
    {
        $content = $this->visibility;
        $content .= ($this->static) ? ' ' . self::KEYWORD_STATIC : '';
        $content .= ' $' . $this->name;
        if (null !== $this->defaultValue) {
            $value = new ValueBlock($this->defaultValue);
            $content .= ' = ' . $value->dump();
        }
        $content .= ';';

        return $content;
    }
}
