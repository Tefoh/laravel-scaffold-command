<?php

namespace Scaffolding\Console;

use Illuminate\Console\GeneratorCommand;
use InvalidArgumentException;
use Scaffolding\Traits\UseField;
use Symfony\Component\Console\Input\InputOption;

class ScaffoldModelCommand extends GeneratorCommand
{
    use UseField;
	
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scaffold:model {name : Name of entity}
                                        {--field=* : Generate columns with this option (e.x. name:string)}
                                        {--fields= : Generate columns with this option (e.x. name:string,email:string)}
                                        {--force : Create the class even if the model already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scaffold the entity.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Model';
    
    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    public function getStub()
    {
        return $this->resolveStubPath('/stubs/scaffold.model.stub');
    }
	
    /**
     * Get the fully-qualified model class name.
     *
     * @param  string  $model
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function parseEntity($entity)
    {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $entity)) {
            throw new InvalidArgumentException('Entity name contains invalid characters.');
        }

        return $this->qualifyModel($entity);
    }

    /**
     * Qualify the given model class base name.
     *
     * @param  string  $model
     * @return string
     */
    protected function qualifyModel(string $model)
    {
        $model = ltrim($model, '\\/');

        $model = str_replace('/', '\\', $model);

        $rootNamespace = $this->rootNamespace();

        if (str_starts_with($model, $rootNamespace)) {
            return $model;
        }

        return is_dir(app_path('Models'))
                    ? $rootNamespace.'Models\\'.$model
                    : $rootNamespace.$model;
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    protected function rootNamespace()
    {
        return $this->laravel->getNamespace();
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
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return is_dir(app_path('Models')) ? $rootNamespace.'\\Models' : $rootNamespace;
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
        $fieldString = $this->getStringColumns($this->option('field'), $this->option('fields'));

        $replace = [
            '{{ columns }}' => $fieldString,
            '{{columns}}' => $fieldString,
        ];

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
    }
}
