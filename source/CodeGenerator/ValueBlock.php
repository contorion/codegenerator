<?php

namespace CodeGenerator;

class ValueBlock extends Block
{
    /** @var mixed */
    private $value;

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    protected function dumpContent()
    {
        if (is_array($this->value)) {
            $array = new ArrayBlock($this->value);

            return $array->dump();
        }

        return var_export($this->value, true);
    }
}
