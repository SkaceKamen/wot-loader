# wot-loader

Application used to load data from raw world of tanks installation into database. This is now used for wot.zipek.cz.

## Requirements

 - PHP 5.4+
 - MySQL
 - Works on both windows and linux

## Installation

You will need composer to install dependencies

```sh
composer install
```

Then create database using `db/wot.sql` file found in this repository. Database name is up to you.

When you're done, copy `values.php.example` as `values.php` and fill in relevant informations.

Now you should be ready to go.