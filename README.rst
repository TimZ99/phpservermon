# PHP Server Monitor

Version 4.0.0.WIP

PHP Server Monitor is a script that checks whether your websites and servers are up and running.
It comes with a web based user interface where you can manage your services and websites,
and you can manage users for each server with a mobile number and email address.

## Install:

```bash
composer install
npm install && npm run build
./vendor/bin/sail up
./vendor/bin/sail artisan migrate
```
### Docker:

```bash
./vendor/bin/sail up
./vendor/bin/sail down
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan migrate:fresh --seed
etc.
```

### Telescope

[Link](localhost/telescope)
