<?php

return [
	
	/*
    |--------------------------------------------------------------------------
    | Scaffold Controller
    |--------------------------------------------------------------------------
    |
    | This determines crud controller created by scaffold command is api based
    | or resource based. resource generates neccessary views for crud.
    | a resource class and a resource collection.
    |
    | Supported: "resource", "api"
    |
	*/

	'controller' => 'resource',

	/*
    |--------------------------------------------------------------------------
    | Scaffold Views
    |--------------------------------------------------------------------------
    |
    | Create needed views from stubs.
    |
	*/
	'views' => ['index', 'create', 'show', 'edit'],
];
