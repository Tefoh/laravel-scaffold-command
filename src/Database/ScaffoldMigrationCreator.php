<?php

namespace Scaffolding\Database;

use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Filesystem\Filesystem;

class ScaffoldMigrationCreator extends MigrationCreator
{
    
    /**
     * Create a new migration creator instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  string  $customStubPath
     * @return void
     */
    public function __construct(Filesystem $files, $customStubPath)
    {
        $this->files = $files;
        $this->customStubPath = $customStubPath;
    }
	

    /**
     * Create a new migration at the given path.
     *
     * @param  string  $name
     * @param  string  $path
     * @param  string|null  $table
     * @param  bool  $create
     * @return string
     *
     * @throws \Exception
     */
    public function createMigration($name, $path, $fields, $table = null, $create = false)
    {
        $this->ensureMigrationDoesntAlreadyExist($name, $path);

        // First we will get the stub file for the migration, which serves as a type
        // of template for the migration. Once we have those we will populate the
        // various place-holders, save the file, and run the post create event.
        $stub = $this->getStub($table, $create);

        $path = $this->getPath($name, $path);

        $this->files->ensureDirectoryExists(dirname($path));

        $this->files->put(
            $path, $this->populateMigrationStub($name, $stub, $table, $fields)
        );

        // Next, we will fire any hooks that are supposed to fire after a migration is
        // created. Once that is done we'll be ready to return the full path to the
        // migration file so it can be used however it's needed by the developer.
        $this->firePostCreateHooks($table);

        return $path;
    }

    /**
     * Populate the place-holders in the migration stub.
     *
     * @param  string  $name
     * @param  string  $stub
     * @param  string|null  $table
     * @return string
     */
    protected function populateMigrationStub($name, $stub, $table, $fields)
    {
        $columns = "";
        foreach ($fields as $key => $field) {
            $columns .= '$table->'. $field .'("'. $key .'");' ."\n\t\t\t";
        }

        $replace = [
            'DummyClass' => $this->getClassName($name),
            '{{ class }}' => $this->getClassName($name),
            '{{class}}' => $this->getClassName($name),
            '{{ columns }}' => $columns,
            '{{columns}}' => $columns,
        ];

        $stub = str_replace(
            array_keys($replace), array_values($replace), $stub
        );

        // Here we will replace the table place-holders with the table specified by
        // the developer, which is useful for quickly creating a tables creation
        // or update migration from the console instead of typing it manually.
        if (! is_null($table)) {
            $replace['{{ table }}'] = $table;
            $replace['{{table}}'] = $table;

            $stub = str_replace(
                array_keys($replace), array_values($replace), $stub
            );
        }

        return $stub;
    }

    /**
     * Get the migration stub file.
     *
     * @param  string|null  $table
     * @param  bool  $create
     * @return string
     */
    protected function getStub($table, $create)
    {
        $stub = $this->files->exists($customPath = $this->customStubPath.'/scaffold.migration.create.stub')
                        ? $customPath
                        : $this->stubPath().'/scaffold.migration.create.stub';

        return $this->files->get($stub);
    }
}
