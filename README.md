# Web App using Kangaroo Rewards REST API - sample app

## Requirements

The following versions of PHP are supported.

* PHP 5.6

## Installation

- Download the zip file or clone https://github.com/kangaroorewards/rest-api-sample-app-php.git in a folder and type in the console:

```
composer install
```

- Rename the configuration file from Config.example to Config.php in "app" folder
- Change the app urls and your Client ID and Secret in the config file
- Configure some permissions. The ```storage``` directory should be writable by your web server
```
chgrp www-data storage
chmod g+w storage
```

## Usage

Navigate to 
```http
http://localhost/index.php
```

## Credits

- [Valentin Ursuleac](https://github.com/ursuleacv)

## License

The MIT License (MIT). Please see [License File](https://github.com/kangaroorewards/rest-api-sample-app-php/blob/master/LICENSE.md) for more information.