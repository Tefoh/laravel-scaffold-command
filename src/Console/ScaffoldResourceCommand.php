<?php

namespace Scaffolding\Console;

use Illuminate\Console\GeneratorCommand;
use Scaffolding\Traits\UseField;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class ScaffoldResourceCommand extends GeneratorCommand
{
	
    use UseField;
	
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scaffold:resource {name : Name of resource}
                                        {--field=* : Generate columns with this option (e.x. name:string)}
                                        {--fields= : Generate columns with this option (e.x. name:string,email:string)}
                                        {--collection : Generate collection resource}
                                        {--resource= : Resource class name}
                                        {--force : Create the class even if the resource already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scaffold the resource.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Resource';
    
    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->collection()
                    ? $this->resolveStubPath('/stubs/scaffold.resource-collection.stub')
                    : $this->resolveStubPath('/stubs/scaffold.resource.stub');
    }
    /**
     * Determine if the command is generating a resource collection.
     *
     * @return bool
     */
    protected function collection()
    {
        return $this->option('collection') ||
               Str::endsWith($this->argument('name'), 'Collection');
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Http\Resources';
    }
	
    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param  string  $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
                        ? $customPath
                        : __DIR__.$stub;
    }
    
    /**
     * Build the class with the given name.
     *
     * Remove the base controller import if we are already in the base namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $this->getAllFields($this->option('field'), $this->option('fields'));

        $replace = [
            '{{ columns }}' => $this->generateFieldString(),
            '{{columns}}' => $this->generateFieldString(),
            '{{ resourceClass }}' => $this->option('resource'),
            '{{resourceClass}}' => $this->option('resource'),
        ];

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
    }

    /**
     * generate coulmns in string for stub.
     * 
     * @return string
     */
    protected function generateFieldString()
    {
		$fieldString = "";
        foreach ($this->columnKeys() as $column) {
			$fieldString .= "\n\t\t\t'{$column}' => ".'$this->'.$column.",";
		}
        return $fieldString;
    }
}
