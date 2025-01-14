<?php

namespace Matemat\TypeGenerator\MigrationFieldHandlers;

use Matemat\TypeGenerator\Interfaces\MigrationFieldHandler;
use Matemat\TypeGenerator\Models\MigrationModel;
use Matemat\TypeGenerator\Traits\GetColumnName;
use Matemat\TypeGenerator\Traits\HandleModifiers;

class EnumHandler implements MigrationFieldHandler
{
    use GetColumnName, HandleModifiers;

    private $field_types = [
        'enum',
    ];

    public function handle(string $fieldType, string $str, MigrationModel $model): bool
    {
        if (in_array($fieldType, $this->field_types)) {
            $column_name = $this->getColumnName($str);
            $values = $this->extractEnumValues($str);
            $model->addField([
                'field_name' => $column_name,
                'field_type' => 'enum',
                'modifiers' => $this->handleModifiers($str),
                'enum_values' => $values,
            ]);

            return true;
        }

        return false;
    }

    private function extractEnumValues(string $str): array
    {
        // Use regex to capture the content inside the square brackets
        preg_match('/\[(.*?)\]/', $str, $matches);

        if (! isset($matches[1])) {
            // Return an empty array if no match is found
            return [];
        }

        // Get the content inside the brackets
        $content = $matches[1];

        // Remove single and double quotes, and split by comma
        $values = array_map('trim', preg_split('/\s*,\s*/', $content));
        $values = array_map(function ($value) {
            return trim($value, '\'"'); // Remove any surrounding quotes
        }, $values);

        return $values;
    }
}
