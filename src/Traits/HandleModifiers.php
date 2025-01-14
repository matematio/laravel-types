<?php

namespace Matemat\TypeGenerator\Traits;

trait HandleModifiers
{
    public function handleModifiers($str)
    {
        $modifiers = [];
        if ($this->checkNullable($str)) {
            array_push($modifiers, 'nullable');
        }

        if ($this->checkInvisible($str)) {
            array_push($modifiers, 'invisible');
        }

        return $modifiers;
    }

    public function checkInvisible($str): bool
    {

        if (preg_match('/->invisible\(/', $str)) {
            return true;
        }

        return false;

    }

    public function checkNullable($str): bool
    {

        if (preg_match('/->nullable\(/', $str)) {
            return true;
        }

        return false;
    }
}
