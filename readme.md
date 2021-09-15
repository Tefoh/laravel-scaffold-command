# laravel scaffold command

## create all necessary files for crud operation with one command
- model
- resource controller, api or just regular crud controller
- migration with fields
- feature tests
- views, if crud is not api
    - index
    - create
    - show
    - edit
- resources, if crud is api
    - resource class
    - resource collection class


## installation
```bash
composer require tefoh/laravel-scaffold-command
```
next
```bash
php artisan vendor:publish --tag=scaffold-stubs
```

### usage
```bash
php artisan scaffold {your entity name}

like php artisan scaffold Category --fields=name:string,slug:string,parent_id:foreignId
```
enjoy :)
