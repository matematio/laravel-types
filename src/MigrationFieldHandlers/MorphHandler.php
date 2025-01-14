<?php

namespace Matemat\TypeGenerator\MigrationFieldHandlers;

use Matemat\TypeGenerator\Interfaces\MigrationFieldHandler;
use Matemat\TypeGenerator\Models\MigrationModel;
use Matemat\TypeGenerator\Traits\GetColumnName;
use Matemat\TypeGenerator\Traits\HandleModifiers;

class MorphHandler implements MigrationFieldHandler
{
    use GetColumnName, HandleModifiers;

    private $field_types = [
        'morphs', 'nullableMorphs', 'nullableUlidMorphs', 'nullableUuidMorphs', 'ulidMorphs', 'uuidMorphs',
    ];

    public function handle(string $fieldType, string $str, MigrationModel $model): bool
    {
        if (in_array($fieldType, $this->field_types)) {
            $column_name = $this->getColumnName($str);
            $modifiers = $this->handleModifiers($str);
            $idType = ['string', 'number'];
            if ($fieldType == 'nullableUlidMorphs' || $fieldType == 'nullableUuidMorphs' || $fieldType == 'ulidMorphs' || 'uuidMorphs') {
                $idType = 'number';
            }

            if ($fieldType == 'nullableMorphs' || $fieldType == 'nullableUlidMorphs' || $fieldType == 'nullableUuidMorphs') {
                $modifiers[] = 'nullable';
            }

            $model->addField([
                'field_name' => $column_name.'_id',
                'field_type' => $idType,
                'modifiers' => $modifiers,
            ]);
            $model->addField([
                'field_name' => $column_name.'_type',
                'field_type' => 'string',
                'modifiers' => $modifiers,
            ]);

            return true;
        }

        return false;
    }
}
