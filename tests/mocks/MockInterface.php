<?php

namespace CodeGeneratormocks;

interface MockInterface extends \CodeGeneratorMocks\MockInterfaceTwo
{
    public function withTypeHinting(\Countable $countable, array $array, callable $callable);

    public function defaultValues($defaultValue = null, $defaultArray = []);

    public function withReferenceParam(&$param);
}
