<?php

namespace Matemat\TypeGenerator\Interfaces;

use Symfony\Component\Finder\SplFileInfo;

interface FileParser
{
    public function parse(SplFileInfo $file): array;
}
