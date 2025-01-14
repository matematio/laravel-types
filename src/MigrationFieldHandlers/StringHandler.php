<?php

namespace Matemat\TypeGenerator\MigrationFieldHandlers;

use Matemat\TypeGenerator\Interfaces\MigrationFieldHandler;
use Matemat\TypeGenerator\Models\MigrationModel;
use Matemat\TypeGenerator\Traits\GetColumnName;
use Matemat\TypeGenerator\Traits\HandleModifiers;

class StringHandler implements MigrationFieldHandler
{
    use GetColumnName, HandleModifiers;

    private $field_types = [
        'char', 'geography', 'geometry', 'ipAddress', 'json', 'jsonb', 'longText', 'macAddress', 'mediumText',
        'rememberToken', 'set', 'string', 'text', 'tinyText',
    ];

    public function handle(string $fieldType, string $str, MigrationModel $model): bool
    {
        if (in_array($fieldType, $this->field_types)) {
            $column_name = $this->getColumnName($str);
            if (! $column_name && $fieldType == 'rememberToken') {
                $column_name = 'remember_token';
            }
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
