Using php_rbac
===========================

Ill outline basic usage of the library below. You should check out the test suite for more
examples if desired. The examples below are basically in the same order as you would use the
library most likely.

Terminology
---------------------------------------

- `Permission` Defines a permission that can be assigned to roles. This can be any string you want
 as long as its unique.



Creating an assignable permission
---------------------------------------

This demonstrates creating a new permission in the database.

```php
<?php
use RBAC\Role\Permission;
use RBAC\Manager\RoleManager;

// Create and populate a Permission instance
$perm = Permission::create("admin_view", "Allows viewing of the admin section");

// Setup the role manager
$role_mgr = new RoleManager(new PDO("..."));

// Save the permission to persistant storage
if ($role_mgr->permissionSave($perm)) {
    // Saved successfully;
    // The $perm should have the permission_id now set
} else {
    // Failed to save permission record.
}
?>
```

Creating a new role
------------------------------------

The following demonstrated creating an empty role with the permission that was created above.

```php
<?php
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
