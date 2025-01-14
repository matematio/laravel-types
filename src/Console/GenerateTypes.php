<?php

namespace Matemat\TypeGenerator\Console;

use Illuminate\Console\Command;
use Matemat\TypeGenerator\TypeGenerator;

class GenerateTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'matemat:generate-types';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates TS interfaces from Laravel migrations';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $generator = app()->make(TypeGenerator::class);
        $generator->generate();
        $this->info('Interfaces generated successfully!');
    }
}
