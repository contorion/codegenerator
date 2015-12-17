<?php

namespace CodeGeneratormocks;

trait MockTraitTwo
{
    /** @var int */
    protected $_bar2 = 1;

    /**
     * @return bool
     */
    public function otherMethod()
    {
        return false;
    }
}
