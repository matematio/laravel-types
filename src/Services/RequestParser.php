<?php

namespace Matemat\TypeGenerator\Services;

use Matemat\TypeGenerator\Interfaces\FileParser;
use Matemat\TypeGenerator\Models\MigrationModel;
use Matemat\TypeGenerator\Traits\CommentRemover;
use SplStack;
use Symfony\Component\Finder\SplFileInfo;

class RequestParser implements FileParser
{
    use CommentRemover;

    private array $rullesMap = [
        'accepted' => [
            'field_type' => 'concrete',
            'field_values' => ['yes', 'on', 1, '1', true, 'true'],
        ],
        'active_url' => ['field_type' => 'string'],
        'alpha' => ['field_type' => 'string'],
        'alpha_dash' => ['field_type' => 'string'],
        'alpha_num' => ['field_type' => 'string'],
        'array' => ['field_type' => 'array'],
        'boolean' => ['field_type' => 'boolean'],
        'contains' => ['field_type' => 'array'],
        'current_password' => ['field_type' => 'string'],
        'date' => ['field_type' => 'string'],
        'date_equals' => ['field_type' => 'string'],
        'decimal' => ['field_type' => 'number'],
        'declined' => [
            'field_type' => 'concrete',
            'field_values' => ['no', 'off', 0, '0', false, 'false'],
        ],
        'digits' => ['field_type' => 'number'],
        'digits_between' => ['field_type' => 'number'],
        'email' => ['field_type' => 'string'],
        'enum' => ['field_type' => 'string'],
        'file' => ['field_type' => 'file'],
        'hex_color' => ['field_type' => 'string'],
        'image' => ['field_type' => 'file'],
        'integer' => ['field_type' => 'number'],
        'ip' => ['field_type' => 'string'],
        'ipv4' => ['field_type' => 'string'],
        'ipv6' => ['field_type' => 'string'],
        'json' => ['field_type' => 'string'],
        'list' => ['field_type' => 'array'],
        'mac_address' => ['field_type' => 'string'],
        'mimetypes' => ['field_type' => 'file'],
        'mimes' => ['field_type' => 'file'],
        'numeric' => ['field_type' => 'number'],
        'string' => ['field_type' => 'string'],
        'timezone' => ['field_type' => 'string'],
        'url' => ['field_type' => 'string'],
        'uuid' => ['field_type' => 'string'],
        'ulid' => ['field_type' => 'string'],
    ];

    public function __construct() {}

    public function parse(SplFileInfo $file): array
    {
        $models = [];
        $content = $file->getContents();
        $content = $this->removeComment($content);
        $modelName = $this->getName($content);
        if ($modelName) {
            $requestType = $this->getType($modelName);
            $fields = $this->getFields($content, $modelName);
            $fields = $this->normalizeFields($fields);
            $model = new MigrationModel($modelName, $requestType);
            foreach ($fields as $fieldName => $rules) {
                $this->handleField($fieldName, $rules, $model);
            }

            $models[] = $model;
        }

        return $models;
    }

    private function getName(string $content): ?string
    {
        if (preg_match('/(?<=\bclass\s)\w+(?=\s+extends)/', $content, $matches)) {
            return trim($matches[0]);
        }

        return null;
    }

    private function getType(string $name): ?string
    {
        if (str_contains($name, 'Update')) {
            return 'update';
        }
        if (str_contains($name, 'Store')) {
            return 'store';
        }

        return null;
    }

    private function getFields(string $content, string $name): array
    {

        $rules = $this->getFilteredRules($content);
        try {
            $result = eval($rules);
            if (is_array($result)) {
                return $result;
            }
        } catch (\Throwable $e) {
            var_dump('Cannot parse FormRequest file:'.$name);
            var_dump($e->getMessage());
        }

        return [];
    }

    private function getFilteredRules(string $content): string
    {
        $allowedChars = ['=', '>', ',', ' ', "\n"];
        preg_match('/rules\(\)\s*.*?(return\s*\[\s*.*)/s', $content, $matches);
        $content = $matches[1];
        $firstFill = false;
        $result = '';
        $start = 0;
        $isDeleting = false;
        $deleteLevel = 0;
        $stack = new SplStack;
        $length = strlen($content);

        for ($i = 0; $i < $length; $i++) {
            if ($stack->isEmpty()) {
                if ($firstFill) {
                    $result = substr($content, 0, $i);
                    $result = $result.';';
                    break;
                }
                if ($content[$i] == '[') {
                    $stack->push($content[$i]);
                    $firstFill = true;
                }
            } else {
                if ($content[$i] == '[' || $content[$i] == '(' || $content[$i] == '{') {
                    $stack->push($content[$i]);

                    continue;
                }
                if ($content[$i] == ']' || $content[$i] == ')' || $content[$i] == '}') {
                    if ($stack->count() == $deleteLevel && $isDeleting) {
                        $isDeleting = false;
                        $content = substr_replace($content, "''", $start, $i - $start);
                        $i = ($i - ($i - $start)) + 2;
                        $length = strlen($content);

                    }
                    $stack->pop();

                    continue;
                }
                if (($content[$i] == "'" && $stack->top() == "'") || ($content[$i] == '"' && $stack->top() == '"')) {
                    $stack->pop();

                    continue;
                }
                if ($content[$i] == '"' || $content[$i] == "'") {
                    $stack->push($content[$i]);

                    continue;
                }
                if ($stack->top() == "'" || $stack->top() == '"') {
                    continue;
                }
                if (! in_array($content[$i], $allowedChars) && ! $isDeleting) {
                    if ($content[$i] == '.') {
                        $content[$i] = ',';

                        continue;
                    }
                    $start = $i;
                    $deleteLevel = $stack->count();
                    $isDeleting = true;

                    continue;
                }
                if ($stack->count() == $deleteLevel && $isDeleting) {

                    if ($content[$i] == ',' || $content[$i] == "\n") {
                        $isDeleting = false;
                        $content = substr_replace($content, "''", $start, $i - $start);
                        $i = ($i - ($i - $start)) + 2;
                        $length = strlen($content);
                    }
                }

            }

        }

        return $result;
    }

    private function normalizeFields(array $rules): array
    {
        foreach ($rules as $name => $rule) {
            if (is_string($rule)) {
                $rules[$name] = explode('|', $rule);
            } else {
                $rules[$name] = array_filter($rule, 'is_string');
            }

        }

        return $rules;
    }

    private function handleField(string $fieldName, array $rules, MigrationModel $model): void
    {
        $fieldTypeLock = false;
        $field = [
            'field_name' => $fieldName,
            'modifiers' => [],
        ];

        // field can be already created via hierarchy builder function
        if ($this->fieldExists($fieldName, $model)) {
            foreach ($model->fields as $key => $field) {
                if (isset($field['field_slug']) && $field['field_slug'] === $fieldName) {
                    foreach ($rules as $rule) {
                        $ruleParts = explode(':', $rule);
                        $ruleName = $ruleParts[0];
                        if ($ruleName == 'nullable') {
                            $model->fields[$key]['modifiers'][] = 'nullable';
                        }
                    }
                }
            }

            return;
        }

        // Handles nested arrays and objects
        if (str_contains($fieldName, '.')) {
            $parts = explode('.', $fieldName);

            // if nested fieldname becomes slug and field name is the last part of slug
            $fieldSlug = $fieldName;
            $fieldName = $this->buildHierarchy($parts, $model, $parts[0]);
            $field['field_slug'] = $fieldSlug;
            $field['field_name'] = $fieldName;
            array_pop($parts);
            $field['parent'] = implode('.', array_filter($parts, function ($part) {
                return $part !== '' && $part !== '*';
            }));
        }

        foreach ($rules as $rule) {
            $ruleParts = explode(':', $rule);
            $ruleName = $ruleParts[0];
            if (isset($this->rullesMap[$ruleName])) {
                $field = array_merge($field, $this->rullesMap[$ruleName]);
            }
            if ($ruleName == 'in') {
                $field['field_type'] = 'concrete';
                $field['field_values'] = explode(',', $ruleParts[1]);
            }
            if ($ruleName == 'nullable') {
                $field['modifiers'][] = 'nullable';
            }
        }

        if (! isset($field['field_type'])) {
            $field['field_type'] = 'any';
        }
        if ($field['field_type'] == 'array' && ! isset($field['field_slug'])) {
            $field['field_slug'] = $field['field_name'];
        }
        $model->addField($field);
    }

    private function buildHierarchy(array $members, MigrationModel $model, string $slug, ?string $parent = null)
    {
        $current = array_shift($members);

        if (empty($members)) {
            return $current;
        }
        if ($members[0] == '') {
            array_shift($members);
        }
        if ($current !== '*') {

            $isArray = $members[0] == '*';
            $field = [
                'field_name' => $current,
                'field_slug' => $slug,
                'field_type' => $isArray ? 'array' : 'object',
                'modifiers' => [],
            ];
            if ($parent) {
                $field['parent'] = $parent;
                $parent = $members[0];
            }

            // skip if hierarchy member is already created
            if (! $this->fieldExists($slug, $model)) {
                $model->addField($field);
            } else {

                // fix the issue where php treats objects as associative arrays
                if (! $isArray) {
                    foreach ($model->fields as $key => $field) {
                        if (isset($field['field_slug']) && $field['field_slug'] == $slug) {
                            $model->fields[$key]['field_type'] = 'object';
                        }
                    }
                }
            }
        }
        $parent = $slug;
        $slug = $slug.'.'.$members[0];

        return $this->buildHierarchy($members, $model, $slug, $parent);
    }

    private function fieldExists(string $slug, MigrationModel $model): bool
    {
        foreach ($model->fields as $field) {
            if (isset($field['field_slug']) && $field['field_slug'] == $slug) {
                return true;
            }
        }

        return false;
    }
}
