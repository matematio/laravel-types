<?php

namespace Matemat\TypeGenerator\Models;

use Matemat\TypeGenerator\Interfaces\MigrationFieldHandler;

class MigrationModel
{
    /*
        field_name, field_type, field_modifiers
    */
    public array $fields = [];

    /** @var MigrationFieldHandler[] */
    private array $field_types = ['any', 'string', 'number', 'boolean', 'enum', 'array', 'object', 'file', 'concrete'];

    public function __construct(public $name, public $type) {}

    public function addField(array $data): bool
    {
        if (! isset($data['field_name']) || ! is_string($data['field_name'])) {
            return false;
        }

        if (! isset($data['field_type'])) {
            return false;
        }

        if (is_string($data['field_type'])) {
            if (! in_array($data['field_type'], $this->field_types)) {
                return false;
            }
        } elseif (is_array($data['field_type'])) {
            if (array_diff($data['field_type'], $this->field_types)) {
                return false;
            }
        } else {
            return false;
        }

        $this->fields[] = $data;

        return true;
    }
}
