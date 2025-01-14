<?php

namespace Matemat\TypeGenerator\MigrationFieldHandlers;

use Matemat\TypeGenerator\Interfaces\MigrationFieldHandler;
use Matemat\TypeGenerator\Models\MigrationModel;
use Matemat\TypeGenerator\Traits\GetColumnName;
use Matemat\TypeGenerator\Traits\HandleModifiers;

class IdHandler implements MigrationFieldHandler
{
    use GetColumnName, HandleModifiers;

    // foreignUlid, foreignUuid, uuid, ulid
    private $field_types = [
        'bigIncrements', 'id', 'foreignId', 'foreign', 'increments', 'mediumIncrements',
        'smallIncrements', 'tinyIncrements',
    ];

    public function handle(string $fieldType, string $str, MigrationModel $model): bool
    {

        if (in_array($fieldType, $this->field_types)) {
            $column_name = $this->getColumnName($str) ?? 'id';
            $model->addField([
                'field_name' => $column_name,
                'field_type' => 'number',
                'modifiers' => $this->handleModifiers($str),
            ]);

            return true;
        }
        if ($fieldType == 'foreignUlid' || $fieldType == 'foreignUuid') {
            $column_name = $this->getColumnName($str);
            $model->addField([
                'field_name' => $column_name,
                'field_type' => 'string',
                'modifiers' => $this->handleModifiers($str),
            ]);

            return true;
        }

        if ($fieldType == 'ulid' || $fieldType == 'uuid') {
            $column_name = $this->getColumnName($str) ?? 'id';
            $model->addField([
                'field_name' => $column_name,
                'field_type' => 'string',
                'modifiers' => $this->handleModifiers($str),
            ]);

            return true;
        }

        return false;
    }
}
