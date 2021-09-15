<?php

namespace Scaffolding\Console;

use Illuminate\Database\Console\Migrations\BaseCommand;
use Illuminate\Database\Console\Migrations\TableGuesser;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;
use Scaffolding\Database\ScaffoldMigrationCreator;
use Scaffolding\Traits\UseField;

class ScaffoldMigrationCommand extends BaseCommand
{
    use UseField;
	
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scaffold:migration {name : The name of the migration}
                                                {--create= : The table to be created}
                                                {--table= : The table to migrate}
                                                {--path= : The location where the migration file should be created}
                                                {--field=* : Generate columns with this option (e.x. name:string)}
                                                {--fields= : Generate columns with this option (e.x. name:string,email:string)}
                                                {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}
                                                {--fullpath : Output the full path of the migration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scaffold the entity migration.';

    /**
     * The migration creator instance.
     *
     * @var \Scaffolding\Database\ScaffoldMigrationCreator
     */
    protected $creator;
	
    /**
     * Create a new migration install command instance.
     *
     * @param  \Scaffolding\Database\ScaffoldMigrationCreator  $creator
     * @return void
     */
    public function __construct(ScaffoldMigrationCreator $creator)
    {
        parent::__construct();

        $this->creator = $creator;
    }


    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // It's possible for the developer to specify the tables to modify in this
        // schema operation. The developer may also specify if this table needs
        // to be freshly created so we can create the appropriate migrations.
        $name = Str::snake(trim($this->input->getArgument('name')));

        $table = $this->input->getOption('table');

        $create = $this->input->getOption('create') ?: false;

        // If no table was given as an option but a create option is given then we
        // will use the "create" option as the table name. This allows the devs
        // to pass a table name into this option as a short-cut for creating.
        if (! $table && is_string($create)) {
            $table = $create;

            $create = true;
        }

        // Next, we will attempt to guess the table name if this the migration has
        // "create" in the name. This will allow us to provide a convenient way
        // of creating migrations that create new tables for the application.
        if (! $table) {
            [$table, $create] = TableGuesser::guess($name);
        }

        // Now we are ready to write the migration out to disk. Once we've written
        // the migration out, we will dump-autoload for the entire framework to
        // make sure that the migrations are registered by the class loaders.
        $this->writeMigration($name, $table, $create, $this->getAllFields($this->option('field'), $this->option('fields')));
    }

    /**
     * Write the migration file to disk.
     *
     * @param  string  $name
     * @param  string  $table
     * @param  bool  $create
     * @return string
     */
    protected function writeMigration($name, $table, $create, $fields)
    {
        $file = $this->creator->createMigration(
            $name, $this->getMigrationPath(), $fields, $table, $create
        );

        if (! $this->option('fullpath')) {
            $file = pathinfo($file, PATHINFO_FILENAME);
        }

        $this->line("<info>Created Migration:</info> {$file}");
    }
}
