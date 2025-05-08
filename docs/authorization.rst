.. _authorization:

Authorization
================================================

Laravel uses **gates** and **policies** to control access throughout the application.  
All gates and policies are defined and registered in ``app/Providers/AuthServiceProvider.php``.

- **Gates** handle simple, global authorization logic that is not tied to a specific model.
- **Policies** handle complex, model-specific authorization logic.

Scopes
+++++++

Usage
-----
- Use policies with: ``Gate::allows('method', $model)``
- Use gates with: ``Gate::allows('gate_name')``
- In Blade templates, use the ``@can`` directive:
  - For policies: ``@can('viewAny', App\Models\Server::class)``
  - For gates: ``@can('not-suspended')``

Defined Policy Methods
----------------------

**ServerPolicy**
- ``view``
- ``viewAny``
- ``manage``
- ``manageAny``
- ``check``
- ``checkAny``

**UserPolicy**
- ``view``
- ``viewAny``
- ``manage``
- ``manageAny``

**Global Gates**
- ``not-suspended``
- ``config:manage``

Gates
+++++

Gates are defined in ``app/Providers/AuthServiceProvider.php`` using ``Gate::define``.  
Gates are used for authorization logic that is not associated with a specific Eloquent model.

Defining a Gate
---------------
``
Gate::define('not-suspended', fn (User $user) =>
$user->isSuspended() ? Response::deny() : Response::allow()
);
``

Policies
++++++++

Policies are PHP classes stored in ``app/Policies``.  
Each policy maps to a model and contains methods that define which actions a user can perform on that model.

Defining a Policy
-----------------

Register policies in ``app/Providers/AuthServiceProvider.php`` using:

```php
Gate::policy(App\Models\Server::class, App\Policies\ServerPolicy::class);
```

Policy methods return a boolean value or a `Response::allow()` / `Response::deny()` object to grant or deny access.
