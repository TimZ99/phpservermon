PHP Server Monitor
==================

! This function is a work in progress and in no way ready to test or work with. There will be breaking changes in the process.

Version 4.0.0.WIP

PHP Server Monitor is a script that checks whether your websites and servers are up and running.
It comes with a web based user interface where you can manage your services and websites,
and you can manage users for each server with a mobile number and email address.

Install:
-------

```bash
composer install
npm install && npm run build
./vendor/bin/sail up
./vendor/bin/sail artisan migrate
```

Docker:
-------

```bash
./vendor/bin/sail up
./vendor/bin/sail down
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan migrate:fresh --seed
etc.
```

Telescope
--------

[Link](http://localhost/telescope)
