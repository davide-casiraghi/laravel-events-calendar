# Laravel Events Calendar

[![Latest Version on Packagist](https://img.shields.io/packagist/v/davide-casiraghi/laravel-events-calendar.svg?style=flat-square)](https://packagist.org/packages/davide-casiraghi/laravel-events-calendar)
[![Build Status](https://img.shields.io/travis/davide-casiraghi/laravel-events-calendar/master.svg?style=flat-square)](https://travis-ci.org/davide-casiraghi/laravel-events-calendar)
[![Quality Score](https://img.shields.io/scrutinizer/g/davide-casiraghi/laravel-events-calendar.svg?style=flat-square)](https://scrutinizer-ci.com/g/davide-casiraghi/laravel-events-calendar)
<a href="https://codeclimate.com/github/davide-casiraghi/laravel-events-calendar/maintainability"><img src="https://api.codeclimate.com/v1/badges/f97a74037f25f1c29088/maintainability" /></a>
[![GitHub last commit](https://img.shields.io/github/last-commit/davide-casiraghi/laravel-events-calendar.svg)](https://github.com/davide-casiraghi/laravel-events-calendar) 


Create and manage calendar events in your Laravel application.  
For each event can be selected: a venue, one or more teachers, one or more organizers.

## Installation

You can install the package via composer:

```bash
composer require davide-casiraghi/laravel-events-calendar
```

### Publish all the vendor files
```php artisan vendor:publish --force```

### Run the database migrations
```php artisan migrate```

### Run the database seeders
```bash
php artisan db:seed --class=ContinentsTableSeeder
php artisan db:seed --class=CountriesTableSeeder
php artisan db:seed --class=EventCategoriesTableSeeder
```
## Usage

``` php
// Usage description here
```

### Testing
You can run unit tests checking the code coverage using this command.   
``` bash
./vendor/bin/phpunit --coverage-html=html   
```
So you can find the reports about the code coverage in this file /html/index.html  

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email davide.casiraghi@gmail.com instead of using the issue tracker.

## Credits

- [Davide Casiraghi](https://github.com/davide-casiraghi)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
