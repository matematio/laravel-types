<?php

namespace Matemat\TypeGenerator\Services;

use Matemat\TypeGenerator\Models\MigrationModel;

class InterfaceMaker
{
    private array $type_map;

    public function __construct()
    {
        $this->type_map = config('type-generator.type_map');
    }

    public function makeInterfaces($models)
    {
        $interfaces = [];
        foreach ($models as $model) {
            $interface_name = ucfirst($this->underscoreToCamelCase($model->name));

            $interface = 'export interface '.$interface_name." { \n";
            foreach ($model->fields as $field) {
                if (in_array('invisible', $field['modifiers'])) {
                    continue;
                }
                if (isset($field['parent'])) {
                    continue;
                }

                if ($field['field_type'] == 'array' || $field['field_type'] == 'object') {
                    $interface = $interface.$this->buildNested($field, $model, $interface);
                } else {
                    $interface = $interface.$this->convertField($field, $interface);
                }

            }
            $interface = $interface.'}';

            $interfaces[$interface_name] = $interface;
        }

        return $interfaces;
    }

    private function convertField($field, &$interface): string
    {
        $result = ' '.$field['field_name'].': ';
        if ($field['field_type'] == 'enum') {
            $enumName = ucfirst($this->underscoreToCamelCase($field['field_name']));
            $result = $result.$enumName;
            $interface = $this->generateEnum($enumName, $field['enum_values']).$interface;
        } elseif ($field['field_type'] == 'concrete') {
            foreach ($field['field_values'] as $index => $value) {
                $result = $result."'".$value."'";
                if ($index !== array_key_last($field['field_values'])) {
                    $result = $result.' | '; // Add a separator (e.g., comma)
                }
            }
        } else {
            if (is_array($field['field_type'])) {
                foreach ($field['field_type'] as $index => $type) {
                    $result = $result.$this->type_map[$type];
                    if ($index !== array_key_last($field['field_type'])) {
                        $result = $result.' | '; // Add a separator (e.g., comma)
                    }
                }
            } else {
                $result = $result.$this->type_map[$field['field_type']];
            }

        }

        if (in_array('nullable', $field['modifiers'])) {
            $result = $result.' | null';
        }
        $result = $result."\n";

        return $result;
    }

    private function buildNested($field, MigrationModel $model, &$interface): string
    {
        if ($this->isPrimitive($field)) {

            return $this->convertField($field, $interface);
        }

        $result = $field['field_name'].": { \n";
        foreach ($model->fields as $childField) {
            if (isset($childField['parent']) && $childField['parent'] == $field['field_slug']) {

                $result = $result.$this->buildNested($childField, $model, $interface);
            }
        }

        $result = $result.'}';
        if ($field['field_type'] == 'array') {
            $result = $result.'[]';
        }
        $result = $result."\n";

        return $result;
    }

    private function isPrimitive($field): bool
    {
        if (is_string($field['field_type'])) {
            return $field['field_type'] != 'object' && $field['field_type'] != 'array';
        }
        foreach ($field['field_type'] as $index => $type) {
            if ($type == 'object' || $type == 'array') {
                return false;
            }
        }

        return true;
    }

    private function generateEnum($name, $values)
    {
        $result = "enum $name { \n";
        foreach ($values as $value) {
            $result = $result.' '.$value.' = '."'$value'".",\n";
        }
        $result = $result."}\n";

        return $result;
    }

    private function underscoreToCamelCase($string)
    {
        $result = str_replace('_', ' ', $string); // Replace underscores with spaces
        $result = ucwords($result); // Capitalize each word
        $result = str_replace(' ', '', $result); // Remove the spaces
        $result = lcfirst($result); // Make the first character lowercase for camelCase

        return $result;
    }
}
