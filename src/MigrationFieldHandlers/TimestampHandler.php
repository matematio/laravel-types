<?php

namespace Matemat\TypeGenerator\MigrationFieldHandlers;

use Matemat\TypeGenerator\Interfaces\MigrationFieldHandler;
use Matemat\TypeGenerator\Models\MigrationModel;
use Matemat\TypeGenerator\Traits\GetColumnName;
use Matemat\TypeGenerator\Traits\HandleModifiers;

class TimestampHandler implements MigrationFieldHandler
{
    use GetColumnName, HandleModifiers;

    // nullableTimestamps, timestampsTz, timestamps, year
    private $field_types = [
        'dateTimeTz', 'dateTime', 'date', 'softDeletesTz', 'softDeletes', 'timeTz', 'time', 'timestampTz', 'timestamp',
    ];

    public function handle(string $fieldType, string $str, MigrationModel $model): bool
    {
        if (in_array($fieldType, $this->field_types)) {
            $column_name = $this->getColumnName($str);
            if (! $column_name && ($fieldType == 'softDeletes' || $fieldType == 'softDeletesTz')) {
                $column_name = 'deleted_at';
            }
            $model->addField([
                'field_name' => $column_name,
                'field_type' => 'string',
                'modifiers' => $this->handleModifiers($str),
            ]);

            return true;
        }

        if ($fieldType == 'timestampsTz' || $fieldType == 'timestamps' || $fieldType == 'nullableTimestamps') {
            $modifiers = $this->handleModifiers($str);
            if ($fieldType == 'nullableTimestamps') {
                array_push($modifiers, 'nullable');
            }
            $model->addField([
                'field_name' => 'created_at',
                'field_type' => 'string',
                'modifiers' => $modifiers,
            ]);
            $model->addField([
                'field_name' => 'updated_at',
                'field_type' => 'string',
                'modifiers' => $modifiers,
            ]);

            return true;
        }

        if ($fieldType == 'year') {
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
