<?php

namespace Matemat\TypeGenerator\MigrationFieldHandlers;

use Matemat\TypeGenerator\Interfaces\MigrationFieldHandler;
use Matemat\TypeGenerator\Models\MigrationModel;
use Matemat\TypeGenerator\Traits\GetColumnName;
use Matemat\TypeGenerator\Traits\HandleModifiers;

class NumberHandler implements MigrationFieldHandler
{
    use GetColumnName, HandleModifiers;

    private $field_types = [
        'bigIntger', 'decimal', 'double', 'float', 'integer', 'mediumInteger', 'smallInteger', 'tinyInteger', 'unsignedBigInteger', 'unsignedInteger',
        'unsignedMediumInteger', 'unsignedSmallInteger', 'unsignedTinyInteger',
    ];

    public function handle(string $fieldType, string $str, MigrationModel $model): bool
    {
        if (in_array($fieldType, $this->field_types)) {
            $column_name = $this->getColumnName($str);

            $model->addField([
                'field_name' => $column_name,
                'field_type' => 'number',
                'modifiers' => $this->handleModifiers($str),
            ]);

            return true;
        }

        return false;
    }
}
