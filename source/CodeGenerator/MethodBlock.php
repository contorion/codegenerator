<?php

namespace CodeGenerator;

class MethodBlock extends FunctionBlock
{
    /** @var string */
    private $_visibility;

    /** @var bool */
    private $_static;

    /** @var bool */
    private $_abstract;

    /**
     * @param string $name
     * @param callable|string|null $body
     */
    public function __construct($name, $body = null)
    {
        $this->setName($name);
        $this->setVisibility('public');
        $this->setStatic(false);
        $this->setAbstract(false);
        parent::__construct($body);
    }

    /**
     * @param string $visibility
     */
    public function setVisibility($visibility)
    {
        $this->_visibility = (string)$visibility;
    }

    /**
     * @param bool $static
     */
    public function setStatic($static)
    {
        $this->_static = (bool)$static;
    }

    /**
     * @param bool $abstract
     */
    public function setAbstract($abstract)
    {
        $this->_abstract = (bool)$abstract;
    }

    /**
     * @param \ReflectionMethod $reflection
     *
     * @return MethodBlock
     */
    public static function buildFromReflection(\ReflectionMethod $reflection)
    {
        $method = new self($reflection->getName());
        $method->extractFromReflection($reflection);

        return $method;
    }

    /**
     * @param \ReflectionFunctionAbstract $reflection
     */
    public function extractFromReflection(\ReflectionFunctionAbstract $reflection)
    {
        parent::extractFromReflection($reflection);
        if ($reflection instanceof \ReflectionMethod) {
            $this->setVisibilityFromReflection($reflection);
            $this->setStaticFromReflection($reflection);
            $this->setAbstractFromReflection($reflection);
        }
    }

    /**
     * @param \ReflectionMethod $reflection
     */
    public function setVisibilityFromReflection(\ReflectionMethod $reflection)
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
     * @param \ReflectionMethod $reflection
     */
    public function setStaticFromReflection(\ReflectionMethod $reflection)
    {
        $this->setStatic($reflection->isStatic());
    }

    /**
     * @param \ReflectionMethod $reflection
     */
    public function setAbstractFromReflection(\ReflectionMethod $reflection)
    {
        $this->setAbstract($reflection->isAbstract());
    }

    protected function _dumpHeader()
    {
        $code = '';
        if ($this->_abstract) {
            $code .= 'abstract ';
        }
        $code .= $this->_visibility;
        if ($this->_static) {
            $code .= ' static';
        }
        $code .= ' ' . parent::_dumpHeader();

        return $code;
    }

    protected function _dumpBody()
    {
        if ($this->_abstract) {
            return ';';
        }

        return parent::_dumpBody();
    }
}
