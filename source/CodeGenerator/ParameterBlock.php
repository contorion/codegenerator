<?php

namespace CodeGenerator;

class ParameterBlock extends Block
{
    /** @var string */
    private $name;

    /** @var string|null */
    private $type;

    /** @var mixed */
    private $defaultValue;

    /** @var bool */
    private $optional;

    /** @var bool */
    private $passedByReference;

    /**
     * @param string $name
     * @param string|null $type
     * @param null $optional
     * @param mixed|null $defaultValue
     * @param bool|null $passedByReference
     *
     * @throws \Exception
     *
     * @internal param bool|null $isOptional
     */
    public function __construct($name, $type = null, $optional = null, $defaultValue = null, $passedByReference = null)
    {
        $this->name = (string)$name;
        if (null !== $type) {
            $this->type = (string)$type;
        }
        $this->optional = (bool)$optional;
        if (null !== $defaultValue) {
            if (!$this->optional) {
                throw new \Exception('Cannot set default value for non-optional parameter');
            }
            $this->defaultValue = $defaultValue;
        }
        $this->passedByReference = (bool)$passedByReference;
    }

    /**
     * @param \ReflectionParameter $reflection
     *
     * @return ParameterBlock
     */
    public static function buildFromReflection(\ReflectionParameter $reflection)
    {
        $type = null;
        if ($reflection->isCallable()) {
            $type = 'callable';
        }
        if ($reflection->isArray()) {
            $type = 'array';
        }
        if ($reflection->getClass()) {
            $type = $reflection->getClass()->getName();
        }
        $defaultValue = null;
        if ($reflection->isDefaultValueAvailable()) {
            $defaultValue = $reflection->getDefaultValue();
        }

        return new self(
            $reflection->getName(),
            $type,
            $reflection->isOptional(),
            $defaultValue,
            $reflection->isPassedByReference()
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @return boolean
     */
    public function isOptional()
    {
        return $this->optional;
    }

    /**
     * @return string
     */
    protected function dumpContent()
    {
        $content = '';
        if ($this->type) {
            $content .= $this->getType() . ' ';
        }
        if ($this->passedByReference) {
            $content .= '&';
        }
        $content .= '$' . $this->name;
        if ($this->optional) {
            $content .= ' = ' . $this->_dumpDefaultValue();
        }

        return $content;
    }

    /**
     * @return null|string
     */
    public function getType()
    {
        $type = $this->type;
        if (!in_array($type, [null, 'array', 'callable'], true)) {
            $type = self::normalizeClassName($type);
        }

        return $type;
    }

    protected function _dumpDefaultValue()
    {
        if (null === $this->defaultValue) {
            return 'null';
        }
        $value = new ValueBlock($this->defaultValue);

        return $value->dump();
    }
}
