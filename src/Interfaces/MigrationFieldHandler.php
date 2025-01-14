<?php

namespace Matemat\TypeGenerator\Interfaces;

use Matemat\TypeGenerator\Models\MigrationModel;

interface MigrationFieldHandler
{
    public function handle(string $fieldType, string $str, MigrationModel $model): bool;
}
