# Type Generator

Composer package to generate TypeScript interfaces from laravel migrations and form requets 

## Installation

1. Install package - using composer

```
composer require matemat/type-generator
```
2. Publish the package's configuration file using the following command:

```
php artisan vendor:publish --tag=tg-config
```

3. Modify your migrations and form requests paths in the configuration file:

```
your_project/config/type-generator.php
```

4. Run the Artisan console command to generate TypeScript interfaces:

```
php artisan app:generate-types 
```
