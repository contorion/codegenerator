<?php

namespace CodeGenerator;

class InterfaceMethodBlock extends FunctionBlock
{
    /**
     * @param callable|null|string $name
     */
    public function __construct($name)
    {
        $this->setName($name);
        parent::__construct();
    }

    /**
     * @param \ReflectionMethod $reflection
     *
     * @return InterfaceMethodBlock
     */
    public static function buildFromReflection(\ReflectionMethod $reflection)
    {
        $method = new self($reflection->getName());
        $method->extractFromReflection($reflection);

        return $method;
    }

    /**
     * @return string
     */
    protected function dumpHeader()
    {
        return self::VISIBILITY_PUBLIC . ' ' . parent::dumpHeader();
    }

    /**
     * @return string
     */
    protected function dumpBody()
    {
        return ';';
    }
}
