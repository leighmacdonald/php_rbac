# Using php_rbac


Ill outline basic usage of the library below. You should check out the test suite for more
examples if desired. The examples below are basically in the same order as you would use the
library most likely.

## Terminology & Class info

- [`Permission`](https://github.com/leighmacdonald/php_rbac/blob/master/src/RBAC/Permission.php) Defines a
 permission that can be assigned to roles. The name can be any string you want as long as its unique.
- [`Role`](https://github.com/leighmacdonald/php_rbac/blob/master/src/RBAC/Role/Role.php) A Role contains a set of
 permissions which have been allocated to it. Anyone who has this role will inherit those permissions.
- [`RoleSet`](https://github.com/leighmacdonald/php_rbac/blob/master/src/RBAC/Role/RoleSet.php) A wrapper around
 a collection of roles which provides some helper functions for working on the set.
- [`RoleManager`](https://github.com/leighmacdonald/php_rbac/blob/master/src/RBAC/Manager/RoleManager.php) The
 management class which takes care of: Talking to the datastore / Creating / Updating of roles and permissions.
- [`Subject`](https://github.com/leighmacdonald/php_rbac/blob/master/src/RBAC/Subject/Subject.php) - A generic
subject which can have roles assigned to it. In the standard use-case of user permissions, you can think of this
interchangably as a User class.

## Creating and updating an assignable permission

This demonstrates creating a new permission in the database. You must create permissions yourself as the
library does not come with any pre set. I recommend using common prefixes for permission sets, for example creating some
admin permissions: "admin_view", "admin_edit" or "user_delete", "user_view", "user_edit".. etc.

```php
<?php
use PDO;
use RBAC\Permission;
use RBAC\Manager\RoleManager;

// Create and populate a Permission instance
$admin_view = Permission::create("admin_view", "Allows viewing of the admin section");

// Setup the role manager
$role_mgr = new RoleManager(new PDO("..."));

// Save the permission to persistant storage
if (!$role_mgr->permissionSave($admin_view )) {
    // Failed to save permission record.
}

// Demonstrates updating an existing Permission
$admin_view->name = "admin_view_all";
if (!$role_mgr->permissionSave($admin_view)) {
    // Handle Error
}
?>
```

## Creating a new role

The following demonstrated creating an empty role with the permission that was created above. Like permissions,
there are no roles by default created so you will have to create all that you require.

```php
<?php
use PDO;
use RBAC\Role\Role;
use RBAC\Manager\RoleManager;

$storage_adapter = new PDOSQLiteAdapter(PDO("..."));

// Setup the role manager
$role_mgr = new RoleManager($storage_adapter);

// Fetch a permission to attach. This assumes this permission was created earlier successfully.
$admin_view = $role_mgr->permissionFetchByName("admin_view");
$admin_edit = $role_mgr->permissionFetchByName("admin_edit");

// Create and populate a Role instance with a permission
$role = Role::create("admin", "Site administrators");
$role->addPermission($admin_view);
$role->addPermission($admin_edit);

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

There are several methods for acheiving this. You can think of a subject interchangably as a User.

- Implementing the [`SubjectInterface`](https://github.com/leighmacdonald/php_rbac/blob/master/src/RBAC/Subject/SubjectInterface.php)
 interface with your own projects classes. `RoleManager->roleAddSubject($your_subject_class);`
- Passing in your unique subject ID using `RoleManager->roleAddSubjectId($your_subject_id);`

### Using the subject_id

Demonstrates adding roles to a provided user ID.

```php
<?php
use PDO;
use RBAC\Role\Role;
use RBAC\Manager\RoleManager;
use RBAC\Subject\Subject;

// The user id of your user that you wish to attach roles to
$user_id = 4;


// Setup the role manager
$storage_adapter = new PDOSQLiteAdapter(PDO("..."));
$role_mgr = new RoleManager($storage_adapter);

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
to attach roles to your own class. There is a minimal implemented subject example located at
[`Subject`](https://github.com/leighmacdonald/php_rbac/blob/master/src/RBAC/Subject/Subject.php)

```php

// Implement your own user class
class User extends Subject {
}

$db_adapter = new PDO("...");
$storage_adapter = new PDOSQLiteAdapter($db_adapter);

// Assuming your user management class will return a user class which implements SubjectInterface or extends Subject
$user_manager = new UserManager($db_adapter);
$user = $user_manager->fetchUser("Dr.Cool");

// Setup the role manager
$role_mgr = new RoleManager($storage_adapter);

// Fetch an existing role called admin
$role = $role_mgr->roleFetchByName("admin");

// Attach the role to the provided user instance
if (!$role_mgr->roleAddSubject($role, $user)) {
    throw new Exception("...");
}
```

### Check a subject for permissions

Demonstrates how to load and check permissions of a subject instance

```php

// $user_manager is pseudo code for your own external user management class. Its assumed that your
// class will return a user class implementing SubjectInterface.
$user = $user_manager->fetchUser("Dr.Cool");

// Populate the roles into the user/subject instance
$role_manager->loadSubjectRoles($user);

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
