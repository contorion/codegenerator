<?php

namespace CodeGenerator;

class ConstantBlock extends Block
{
    /** @var string */
    private $name;

    /** @var string|int */
    private $value;

    /**
     * @param string $name
     * @param string|int $value
     */
    public function __construct($name, $value)
    {
        $this->name = (string)$name;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    protected function dumpContent()
    {
        return 'const ' . $this->name . ' = ' . var_export($this->value, true) . ';';
    }
}
