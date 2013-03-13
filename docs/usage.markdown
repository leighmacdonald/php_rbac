# Using php_rbac


Ill outline basic usage of the library below. You should check out the test suite for more
examples if desired. The examples below are basically in the same order as you would use the
library most likely.

## Terminology

- [`Permission`](https://github.com/leighmacdonald/php_rbac/blob/master/src/RBAC/Role/Permission.php) Defines a
 permission that can be assigned to roles. This can be any string you want as long as its unique.
- [`Role`](https://github.com/leighmacdonald/php_rbac/blob/master/src/RBAC/Role/Role.php) A Role contains a set of
 permissions which have been allocated to it. Anyone who has this role will inherit those permissions.
- [`RoleSet`](https://github.com/leighmacdonald/php_rbac/blob/master/src/RBAC/Role/RoleSet.php) A wrapper around
 a collection of roles which provides some helper functions for working on the set.
- [`RoleManager`](https://github.com/leighmacdonald/php_rbac/blob/master/src/RBAC/Manager/RoleManager.php) The
 management class which takes care of: Talking to the datastore / Creating / Updating of roles and permissions.

## Creating and updating an assignable permission

This demonstrates creating a new permission in the database.

```php
<?php
use PDO;
use RBAC\Role\Permission;
use RBAC\Manager\RoleManager;

// Create and populate a Permission instance
$perm = Permission::create("admin_view", "Allows viewing of the admin section");

// Setup the role manager
$role_mgr = new RoleManager(new PDO("..."));

// Save the permission to persistant storage
if (!$role_mgr->permissionSave($perm)) {
    // Failed to save permission record.
}

// Demonstrates updating an existing Permission
$perm->name = "admin_load";
if (!$role_mgr->permissionSave($perm)) {
    // Handle Error
}
?>
```

## Creating a new role

The following demonstrated creating an empty role with the permission that was created above.

```php
<?php
use PDO;
use RBAC\Role\Role;
use RBAC\Manager\RoleManager;

// Setup the role manager
$role_mgr = new RoleManager(new PDO("..."));

// Fetch a permission to attach. This assumes this permission was created earlier successfully.
$perm = $role_mgr->permissionFetchByName("admin_view");

// Create and populate a Role instance with a permission
$role = Role::create("admin", "Site administrators");
$role->addPermission($perm)

// Save the permission to persistant storage
if ($role_mgr->roleSave($role)) {
    // Saved successfully;
    // The $role should have the role_id now set
} else {
    // Failed to save role.
}
?>
```

## Assigning Roles to your own projects users

There are several methods for acheiving this.

- Implementing the [`UserInterface`](https://github.com/leighmacdonald/php_rbac/blob/master/src/RBAC/UserInterface.php)
 interface with your own projects classes. `RoleManager->roleAddUser($your_user_class);`
- Passing in your unique user ID using `RoleManager->roleAddUserId($your_user_id);`

### Using the user_id

Demonstrates adding roles to a provided UID.

```php
<?php
use PDO;
use RBAC\Role\Role;
use RBAC\Manager\RoleManager;

// The user id of your user that you wish to attach roles to
$user_id = 4;

// Setup the role manager
$role_mgr = new RoleManager(new PDO("..."));

// Fetch an existing role called admin
$role = $role_mgr->roleFetchByName("admin");

// Attach the role to the provided user_id
if ($role_mgr->roleAddUserId($role, $user_id)) {
    // Saved successfully;
} else {
    // Failed to add...
}
?>
```

### Using the UserInterface

This demonstrates using the [`UserInterface`](https://github.com/leighmacdonald/php_rbac/blob/master/src/RBAC/UserInterface.php)
to attach roles to your own class.

```php
<?php
use PDO;
use RBAC\Role\Role;
use RBAC\Manager\RoleManager;
use RBAC\UserInterface;

class User implements UserInterface
{
    private $user_id;

    /**
     * @var RoleSet
     */
    private $roles = [];

    public function __construct($user_id)
    {
        $this->user_id = $user_id;
    }

    public function id() {
        return $this->user_id;
    }

    public function loadRoleSet(RoleSet $role_set)
    {
        $this->roles = $role_set;
    }

    public function getRoleSet()
    {
        return $this->roles;
    }
}

// The user id of your user that you wish to attach roles to
$user = new User(4);

// Setup the role manager
$role_mgr = new RoleManager(new PDO("..."));

// Fetch an existing role called admin
$role = $role_mgr->roleFetchByName("admin");

// Attach the role to the provided user instance'd id
if ($role_mgr->roleAddUser($role, $user)) {
    // Saved successfully;
} else {
    // Failed to add...
}
?>
```
