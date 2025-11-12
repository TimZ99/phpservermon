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

When you run Sail we now boot three containers: the main `PSM-test` web app, `queue-worker` (runs `php artisan queue:work --queue=curl,default`) and `scheduler` (runs `php artisan schedule:work`). Keeping all three services up is optional but recommended because background checks, heartbeats, and pruning tasks stay off the request cycle, improving the overall experience.

Background workers
-------------------

Regardless of deployment method you must keep both the scheduler and queue worker alive:

* **Docker/Sail:** the `queue-worker` and `scheduler` services start automatically once you execute `./vendor/bin/sail up`.
* **Process supervisor:** on bare metal you can use Supervisor/systemd/etc. to run `php artisan queue:work --queue=curl,default --tries=1` and `php artisan schedule:work` as persistent services.
* **Cron:** if you prefer cron for scheduling, add `* * * * * php /path/to/artisan schedule:run` to cron and still keep a supervised queue worker online.

Without both processes, the heartbeat falls back to `sync` execution and checks will run inline.

Telescope
--------

[Link](http://localhost/telescope)
