<?php

namespace Matemat\TypeGenerator;

use Illuminate\Support\Facades\File;
use Matemat\TypeGenerator\Helpers\ModelHelper;
use Matemat\TypeGenerator\Interfaces\FileParser;
use Matemat\TypeGenerator\Services\FileWriter;
use Matemat\TypeGenerator\Services\InterfaceMaker;
use Matemat\TypeGenerator\Services\MigrationParser;
use Matemat\TypeGenerator\Services\RequestParser;

class TypeGenerator
{
    private string $migpath;

    private string $reqpath;

    private array $blacklist;

    private string $destination;

    public function __construct(
        private readonly MigrationParser $migrationParser,
        private readonly RequestParser $requestParser,
        private readonly InterfaceMaker $intmaker,
        private readonly FileWriter $fileWriter,
        private readonly ModelHelper $modelHelper,
    ) {

        $this->migpath = base_path(config('type-generator.migrations_path'));
        $this->reqpath = base_path(config('type-generator.requests_path'));
        $this->blacklist = config('type-generator.blacklist');
        $this->destination = base_path(config('type-generator.destination_folder'));
    }

    public function generate(): void
    {
        $this->generateModels();
        $this->generateRequests();
    }

    private function generateModels(): void
    {

        $interfaces = $this->proceedFiles($this->migpath, $this->migrationParser, true);

        $this->fileWriter->writeMigrationInterfaces($interfaces, $this->destination);
    }

    private function generateRequests(): void
    {
        $interfaces = $this->proceedFiles($this->reqpath, $this->requestParser);
        $this->fileWriter->writeRequestInterfaces($interfaces, $this->destination);
    }

    private function proceedFiles($path, FileParser $parser, bool $merge = false)
    {
        $files = File::allFiles($path);
        $models = [];
        foreach ($files as $file) {

            $filename = $file->getBasename();
            if (in_array($filename, $this->blacklist)) {
                continue;
            }

            array_push($models, ...$parser->parse($file));
        }
        if ($merge) {
            $models = $this->modelHelper->mergeModels($models);
        }
        $interfaces = $this->intmaker->makeInterfaces($models);

        return $interfaces;
    }
}
