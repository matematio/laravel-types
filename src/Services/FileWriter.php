<?php

namespace Matemat\TypeGenerator\Services;

use Illuminate\Support\Facades\File;

class FileWriter
{
    public function __construct() {}

    public function writeMigrationInterfaces($interfaces, $destination): void
    {
        if (! File::exists($destination)) {
            File::makeDirectory($destination, 0777, true);
        }

        $allInterfaces = '';
        foreach ($interfaces as $interface) {
            $allInterfaces .= $interface . PHP_EOL . PHP_EOL; 
        }

        $filePath = $destination.'/models.d.ts';
        File::put($filePath, $allInterfaces);
    }

    public function writeRequestInterfaces($interfaces, $destination): void
    {
        if (! File::exists($destination)) {
            File::makeDirectory($destination, 0777, true);
        }
        $allInterfaces = '';
        foreach ($interfaces as $interface) {
            $allInterfaces .= $interface . PHP_EOL . PHP_EOL; 
        }

        $filePath = $destination.'/requests.d.ts';
        File::put($filePath, $allInterfaces);
    }
}
