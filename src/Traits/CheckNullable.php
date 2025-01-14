<?php

namespace Matemat\TypeGenerator\Traits;

trait CheckNullable
{
    public function checkNullable($str): bool
    {

        if (preg_match('/->nullable\(/', $str)) {
            return true;
        }

        return false;
    }
}
