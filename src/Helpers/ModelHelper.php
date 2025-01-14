<?php

namespace Matemat\TypeGenerator\Helpers;

class ModelHelper
{
    public function mergeModels($models)
    {
        foreach ($models as $index => $model) {

            if ($model->type === 'update') {
                continue;
            }

            foreach ($models as $updateIndex => $updateModel) {
                if ($updateModel->type === 'update' && $updateModel->name === $model->name) {
                    foreach ($updateModel->fields as $updateField) {
                        $found = false;
                        // Iterate through fields in the create model to find and replace matching field_name
                        foreach ($model->fields as $key => $modelField) {
                            if ($modelField['field_name'] === $updateField['field_name']) {
                                // Replace field in model
                                $model->fields[$key] = $updateField;
                                $found = true;
                                break;
                            }
                        }
                        // If the field doesn't exist in the create model, add it
                        if (! $found) {
                            $model->fields[] = $updateField;
                        }
                    }
                    unset($models[$updateIndex]);
                }

            }

        }

        return $models;
    }
}
