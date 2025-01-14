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

        foreach ($interfaces as $name => $interface) {
            $filePath = $destination.'/'.$name.'.ts';
            File::put($filePath, $interface);
        }
    }

    public function writeRequestInterfaces($interfaces, $destination): void
    {
        if (! File::exists($destination)) {
            File::makeDirectory($destination, 0777, true);
        }
        $files = File::allFiles($destination);

        foreach ($interfaces as $name => $interface) {
            $baseResourceName = str_replace(['Request', 'Store', 'Update'], '', $name);
            $resourceFound = false;
            foreach ($files as $file) {
                $fileResource = str_replace('s.ts', '', $file->getBasename());
                if ($fileResource == $baseResourceName) {
                    $interface = "\n".$interface."\n";
                    File::append($file->getPathname(), $interface);
                    $resourceFound = true;
                    break;
                }
            }
            if (! $resourceFound) {
                $filePath = $destination.'/'.$name.'.ts';
                File::put($filePath, $interface);
            }
        }
    }
}
