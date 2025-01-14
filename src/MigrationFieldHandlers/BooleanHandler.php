<?php

namespace Matemat\TypeGenerator\MigrationFieldHandlers;

use Matemat\TypeGenerator\Interfaces\MigrationFieldHandler;
use Matemat\TypeGenerator\Models\MigrationModel;
use Matemat\TypeGenerator\Traits\GetColumnName;
use Matemat\TypeGenerator\Traits\HandleModifiers;

class BooleanHandler implements MigrationFieldHandler
{
    use GetColumnName, HandleModifiers;

    private $field_types = [
        'boolean',
    ];

    public function handle(string $fieldType, string $str, MigrationModel $model): bool
    {
        if (in_array($fieldType, $this->field_types)) {
            $column_name = $this->getColumnName($str);
            $model->addField([
                'field_name' => $column_name,
                'field_type' => 'boolean',
                'modifiers' => $this->handleModifiers($str),
            ]);

            return true;
        }

        return false;
    }
}
