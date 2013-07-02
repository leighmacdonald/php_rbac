CREATE TABLE IF NOT EXISTS "auth_permission" (
    "permission_id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "name"          INTEGER  NOT NULL,
    "description"   TEXT,
    "added_on"      DATETIME NULL DEFAULT current_timestamp,
    "updated_on"    DATETIME NULL DEFAULT current_timestamp,
    UNIQUE ("name" ASC)
);

CREATE TABLE IF NOT EXISTS "auth_role" (
    "role_id"     INTEGER PRIMARY KEY AUTOINCREMENT,
    "name"        TEXT     NOT NULL,
    "description" TEXT,
    "added_on"    DATETIME NULL,
    "updated_on"  DATETIME NULL DEFAULT current_timestamp,
    UNIQUE ("name" ASC)
);

CREATE TABLE IF NOT EXISTS "auth_role_permissions" (
    "role_permission_id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "role_id"            INTEGER  NOT NULL,
    "permission_id"      INTEGER  NOT NULL,
    "added_on"           DATETIME NULL DEFAULT current_timestamp,
    FOREIGN KEY ("permission_id") REFERENCES "auth_permission" ("permission_id")
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY ("role_id") REFERENCES "auth_role" ("role_id")
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    UNIQUE ("role_id" ASC, "permission_id" ASC)
);

CREATE TABLE IF NOT EXISTS "auth_subject_role" (
    "subject_role_id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "subject_id"      INTEGER NOT NULL,
    "role_id"         INTEGER NOT NULL,
    FOREIGN KEY ("role_id") REFERENCES "auth_role" ("role_id")
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    UNIQUE ("subject_id" ASC, "role_id" ASC)
);
