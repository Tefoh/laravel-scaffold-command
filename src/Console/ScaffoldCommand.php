<?php

namespace Scaffolding\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ScaffoldCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scaffold {entity : the name of entity}
                                        {--field=* : Generate columns with this option (e.x. --field=name:string)}
                                        {--fields= : Generate columns with this option (e.x. --fields=name:string,email:string)}
                                        {--api : Generate controller api resource with Resource class}
                                        {--force : Create the class even if the model already exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'scaffold the entity for files needed based on configuration';

    /**
     * Accepted types for given fields.
     * Doc: https://laravel.com/docs/8.x/migrations#available-column-types
     *
     * @var string[]
     */
    protected $acceptedTypes = [
        'bigIncrements',
        'bigInteger',
        'binary',
        'boolean',
        'char',
        'dateTimeTz',
        'dateTime',
        'date',
        'decimal',
        'double',
        'enum',
        'float',
        'foreignId',
        'foreignUuid',
        'geometryCollection',
        'geometry',
        'id',
        'increments',
        'integer',
        'ipAddress',
        'json',
        'jsonb',
        'lineString',
        'longText',
        'macAddress',
        'mediumIncrements',
        'mediumInteger',
        'mediumText',
        'morphs',
        'multiLineString',
        'multiPoint',
        'multiPolygon',
        'nullableMorphs',
        'nullableTimestamps',
        'nullableUuidMorphs',
        'point',
        'polygon',
        'rememberToken',
        'set',
        'smallIncrements',
        'smallInteger',
        'softDeletesTz',
        'softDeletes',
        'string',
        'text',
        'timeTz',
        'time',
        'timestampTz',
        'timestamp',
        'timestampsTz',
        'timestamps',
        'tinyIncrements',
        'tinyInteger',
        'tinyText',
        'unsignedBigInteger',
        'unsignedDecimal',
        'unsignedInteger',
        'unsignedMediumInteger',
        'unsignedSmallInteger',
        'unsignedTinyInteger',
        'uuidMorphs',
        'uuid',
        'year',
    ];

    protected $crudViews = [
        'index', 'create', 'show', 'edit'
    ];

    /**
     * The Composer instance.
     *
     * @var \Illuminate\Support\Composer
     */
    protected $composer;

	
    /**
     * Create a new migration install command instance.
     *
     * @param  \Illuminate\Support\Composer  $composer
     * @return void
     */
    public function __construct(Composer $composer)
    {
        parent::__construct();

        $this->composer = $composer;
    }

    /**
     * Execute the console command that create all files.
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function handle()
    {
        $this->validateInput();

        $this->scaffoldModel();
        $this->scaffoldMigration();
        $this->scaffoldController();

        if ($this->option('api') || config('scaffold.controller', 'resource') === 'api') {
            $this->scaffoldResources();
        } else {
            $this->scaffoldViews();
        }

        $this->scaffoldTest();
        
        $this->composer->dumpAutoloads();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function validateInput()
    {
        $fieldInput = $this->option('field') ?? [];
        $fieldsInput = $this->option('fields') ? explode(',', $this->option('fields')) : [];

        if (count($fieldInput) < 1 && count($fieldsInput) < 1) {
            throw new InvalidArgumentException('Plese provide fields.');
        }

        if (count($fieldInput) > 0) {
            $this->validateFields($fieldInput, $this->filteredFields($fieldInput));
        }

        if (count($fieldsInput) > 0) {
            $this->validateFields($fieldsInput, $this->filteredFields($fieldsInput));
        }
    }
    
    /**
     * validate if given fields is correct. field should specify type 
     * and the type should be one of accepted ones.
     * 
     * @param array $fields
     * @param array $filteredField
     * 
     * @throws InvalidArgumentException
     */
    protected function validateFields(array $fields, array $filteredField)
    {
        $this->validateFieldHasType($fields, $filteredField);
        $this->validateFieldTypeIsCorrect($fields);
    }

    /**
     * validate if given fields specified type.
     * 
     * @param array $fields
     * @param array $filteredField
     * 
     * @throws InvalidArgumentException
     */
    protected function validateFieldHasType(array $fields, array $filteredField)
    {
        if (count($filteredField ?? []) !== count($fields ?? [])) {
            throw new InvalidArgumentException('Field name should also specify the type.');
        }
    }

    /**
     * validate if given fields type is one of accepted ones.
     * 
     * @param array $fields
     * 
     * @throws InvalidArgumentException
     */
    protected function validateFieldTypeIsCorrect(array $fields)
    {
        foreach ($fields as $field) {
            if (! in_array(explode(':', $field)[1], $this->acceptedTypes)) {
                throw new InvalidArgumentException('Field type must be one of accepted types. checkout https://laravel.com/docs/8.x/migrations#available-column-types');
            }
        }
    }

    /**
     * filter fields not contains type.
     * 
     * @param array $fields
     * 
     * @return array
     */
    protected function filteredFields(array $fields)
    {
        return array_filter(
            $fields,
            fn ($field) => str_contains($field, ':')
        );
    }

    /**
     * scaffold model class.
     */
    protected function scaffoldModel()
    {
        $this->call('scaffold:model', [
            'name' => $this->argument('entity'),
            '--field' => $this->option('field'),
            '--fields' => $this->option('fields'),
        ]);
    }

    /**
     * scaffold migration with given fields.
     */
    protected function scaffoldMigration()
    {
        $this->call('scaffold:migration', [
            'name' => 'create_'. Str::plural(strtolower($this->argument('entity'))) .'_table',
            '--field' => $this->option('field'),
            '--fields' => $this->option('fields'),
        ]);
    }

    /**
     * scaffold resource controller, with needed crud actions.
     */
    protected function scaffoldController()
    {
        $this->call('scaffold:controller', [
            'name' => $this->argument('entity').'Controller',
            '--entity' => $this->argument('entity'),
            '--api' => $this->option('api'),
        ]);
    }

    /**
     * scaffold a resource and resource collection class for api crud controller.
     */
    protected function scaffoldResources()
    {
        $entityName = $this->argument('entity');
        $this->call('scaffold:resource', [
            'name' => $entityName.'Collection',
            '--resource' => $entityName.'Resource',
            '--field' => $this->option('field'),
            '--fields' => $this->option('fields'),
        ]);
        $this->call('scaffold:resource', [
            'name' => $entityName.'Resource',
            '--field' => $this->option('field'),
            '--fields' => $this->option('fields'),
        ]);
    }

    /**
     * scaffold crud views.
     */
    protected function scaffoldViews()
    {
        $views = config('scaffold.views', $this->crudViews);

        collect($views)
                ->each(function($item) {
                    $this->call('scaffold:view', [
                        'name' => $this->argument('entity'),
                        '--file' => $item,
                    ]);
                });
    }

    /**
     * scaffold a basic feature test.
     */
    protected function scaffoldTest()
    {
        $this->call('make:test', [
            'name' => $this->argument('entity').'Test'
        ]);
    }
}
