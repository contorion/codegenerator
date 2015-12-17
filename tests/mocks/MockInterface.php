<?php

namespace CodeGeneratormocks;

interface MockInterface
{
    public function withTypeHinting(\Countable $countable, array $array, callable $callable);

    public function defaultValues($defaultValue = null, $defaultArray = []);

    public function withReferenceParam(&$param);
}
