<?php

namespace Matemat\TypeGenerator\Services;

use Matemat\TypeGenerator\Interfaces\FileParser;
use Matemat\TypeGenerator\MigrationFieldHandlers\BooleanHandler;
use Matemat\TypeGenerator\MigrationFieldHandlers\EnumHandler;
use Matemat\TypeGenerator\MigrationFieldHandlers\IdHandler;
use Matemat\TypeGenerator\MigrationFieldHandlers\MorphHandler;
use Matemat\TypeGenerator\MigrationFieldHandlers\NumberHandler;
use Matemat\TypeGenerator\MigrationFieldHandlers\StringHandler;
use Matemat\TypeGenerator\MigrationFieldHandlers\TimestampHandler;
use Matemat\TypeGenerator\Models\MigrationModel;
use Matemat\TypeGenerator\Traits\CommentRemover;
use Symfony\Component\Finder\SplFileInfo;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MigrationParser implements FileParser
{
    use CommentRemover;

    private array $handlers = [];
    private string $modelpath;
    private array   $modelFiles;
    private array $namingMap;

    public function __construct()
    {
        $this->handlers = [
            new IdHandler,
            new StringHandler,
            new NumberHandler,
            new BooleanHandler,
            new EnumHandler,
            new MorphHandler,
            new TimestampHandler,
        ];
        $this->modelpath = base_path(config('type-generator.models_path'));
        $this->modelFiles = File::allFiles($this->modelpath);
        $this->createNamingMap();
    }

    public function parse(SplFileInfo $file): array
    {
        $models = [];
        $content = $file->getContents();
        $content = $this->removeComment($content);
        $builders = $this->findBuilders($content);
        // Foreach builder (Schema::creat|table)
        foreach ($builders as $builder) {
            $models[] = $this->convertToModel($builder);
        }

        return $models;
    }

    // Schema::create | Schema::table etc ...
    private function findBuilders($content): array
    {
        $builders = [];
        $pattern = '/Schema::(create|table)\(.*?\{.*?\}\);/s';
        preg_match_all($pattern, $content, $matches);

        foreach ($matches[0] as $content) {
            preg_match("/['\"]([^'\"]+)['\"]/", $content, $table_name);
            $builders[] = [
                'content' => $content,
                'table' => $table_name[1] ?? null,
            ];
        }
        foreach ($matches[1] as $index => $type) {
            $builders[$index]['type'] = $type;
        }

        return $builders;
    }

    private function convertToModel($builder): MigrationModel
    {
        $builder['type'] = $builder['type'] == 'table' ? 'update' : $builder['type'];
        $tableName = $builder['table'];
        if(isset($this->namingMap[$tableName])){
            $tableName = $this->namingMap[$tableName];
        }else{
           $tableName = Str::singular($tableName);
        }

        $model = new MigrationModel($tableName, $builder['type']);
        preg_match_all('/\$table->[^;]+;/', $builder['content'], $matches);
        foreach ($matches[0] as $match) {
            $this->handleField($match, $model);
        }

        return $model;
    }

    private function handleField(string $str, MigrationModel $model)
    {
        preg_match('/\$table->([^\(]+)/', $str, $field_type);
        $field_type = $field_type[1] ?? null;
        if ($field_type == null || $this->isIndex($field_type)) {
            return;
        }

        foreach ($this->handlers as $handler) {
            $handeled = $handler->handle($field_type, $str, $model);
            if ($handeled) {
                break;
            }
        }
    }

    private function isIndex($fieldType): bool
    {
        $indexes = ['primary', 'unique', 'index', 'fullText', 'spatialIndex'];

        return in_array($fieldType, $indexes);
    }

    private function createNamingMap(){
        foreach($this->modelFiles as $model){
            $modelname = strtolower(explode('.',$model->getFilename())[0]);
            $content = $model->getContents();
            $content = $this->removeComment($content);
            
            preg_match('/protected\s*\$table\s*=\s*[\'"]([^\'"]+)[\'"]\s*;/', $content, $matches);
            $tableName = $matches[1] ?? null; //
            if($tableName){
                $this->namingMap[trim($tableName)] = $modelname;
            }
        }
    }
}
