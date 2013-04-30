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
use RBAC\Permission;
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

- Implementing the [`SubjectInterface`](https://github.com/leighmacdonald/php_rbac/blob/master/src/RBAC/Subject/SubjectInterface.php)
 interface with your own projects classes. `RoleManager->roleAddUser($your_user_class);`
- Passing in your unique user ID using `RoleManager->roleAddUserId($your_user_id);`

### Using the subject_id

Demonstrates adding roles to a provided UID.

```php
<?php
use PDO;
use RBAC\Role\Role;
use RBAC\Manager\RoleManager;
use RBAC\Subject\Subject;

// The user id of your user that you wish to attach roles to
$user_id = 4;

// Setup the role manager
$role_mgr = new RoleManager(new PDO("..."));

// Fetch an existing role called admin
$role = $role_mgr->roleFetchByName("admin");

// Attach the role to the provided user_id
if ($role_mgr->roleAddSubjectId($role, $user_id)) {
    // Saved successfully;
} else {
    // Failed to add...
}
?>
```

### Using the Subject/SubjectInterface to extend your own user class

This demonstrates using the [`SubjectInterface`](https://github.com/leighmacdonald/php_rbac/blob/master/src/RBAC/Subject/SubjectInterface.php)
to attach roles to your own class. There is a very basic subject example located at
[`Subject`](https://github.com/leighmacdonald/php_rbac/blob/master/src/RBAC/Subject/Subject.php)

```php

// The user id of your user that you wish to attach roles to. This user_id should be setup by your own
user management system.

// Implement your own user class
class User extends Subject {
}

$db = new PDO("...");

// Assuming your user management class will return
$user_manager = new UserManager($db);
$user = $user_manager->fetchUser("Dr.Cool");

// Setup the role manager
$role_mgr = new RoleManager($db);

// Fetch an existing role called admin
$role = $role_mgr->roleFetchByName("admin");

// Attach the role to the provided user instance
if (!$role_mgr->roleAddSubject($role, $subject)) {
    throw new Exception("...");
}

// Check if a user belongs to the permission provided
if (!$user->hasPermission("admin_view")) {
    throw new InsufficientPermission("Permission denied");
}

// Or using an existing permission instance
$permission = $role_mgr->permissionFetchById(1);
if (!$user->hasPermission($permission)) {
    throw new InsufficientPermission("Permission denied");
}

// Instead of manually throwing you can also use the convienence function provided in the Subject class
// which will throw when the permission isnt found.
try {
    $user->checkPermission($permission);
    // or
    $user->checkPermission("admin_view");
} catch (InsufficientPermission $perm_err) {
    // handle
}

?>
```
