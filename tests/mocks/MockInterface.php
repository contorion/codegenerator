<?php

namespace CodeGeneratorMocks;

interface MockInterface
{
    public function withTypeHinting(\Countable $countable, array $array, callable $callable);

    public function defaultValues($defaultValue = null, $defaultArray = []);

    public function withReferenceParam(&$param);
}
