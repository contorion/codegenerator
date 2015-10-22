<?php

namespace CodeGenerator;

class MethodBlock extends FunctionBlock
{
    /** @var string */
    private $visibility;

    /** @var bool */
    private $static;

    /** @var bool */
    private $abstract;

    /**
     * @param string $name
     * @param callable|string|null $body
     */
    public function __construct($name, $body = null)
    {
        $this->setName($name);
        $this->setVisibility(self::VISIBILITY_PUBLIC);
        $this->setStatic(false);
        $this->setAbstract(false);
        parent::__construct($body);
    }

    /**
     * @param string $visibility
     */
    public function setVisibility($visibility)
    {
        $this->visibility = (string)$visibility;
    }

    /**
     * @param bool $static
     */
    public function setStatic($static)
    {
        $this->static = (bool)$static;
    }

    /**
     * @param bool $abstract
     */
    public function setAbstract($abstract)
    {
        $this->abstract = (bool)$abstract;
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

    protected function dumpHeader()
    {
        $code = '';
        if ($this->abstract) {
            $code .= self::KEYWORD_ABSTRACT . ' ';
        }
        $code .= $this->visibility;
        if ($this->static) {
            $code .= ' ' . self::KEYWORD_STATIC;
        }
        $code .= ' ' . parent::dumpHeader();

        return $code;
    }

    protected function dumpBody()
    {
        if ($this->abstract) {
            return ';';
        }

        return parent::dumpBody();
    }
}
