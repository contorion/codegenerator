<?php

namespace CodeGeneratormocks;

trait MockTrait
{
    /**
     * @var array
     */
    public $foo = [1, 2];

    /**
     * @var int
     */
    protected $_bar = 1;

    private $_foo;

    public static function staticMethod()
    {
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->foo);
    }

    public function withTypeHinting(\Countable $countable, array $array, callable $callable)
    {
        echo 1;
    }

    public function defaultValues($defaultValue = null, $defaultArray = [])
    {
        echo 2;
    }

    public function withReferenceParam(&$param)
    {
    }

    protected function abstractMethod()
    {
    }

    private function _foo()
    {
        // comment
        // indentation
        // back
    }
}
