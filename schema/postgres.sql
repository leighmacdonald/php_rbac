CREATE SEQUENCE auth_role_id_seq INCREMENT 1 START 1;
CREATE SEQUENCE auth_permission_id_seq INCREMENT 1 START 1;
CREATE SEQUENCE auth_role_permissions_seq INCREMENT 1 START 1;

CREATE TABLE "auth_role" (
  "role_id"     INT4 DEFAULT nextval('auth_role_id_seq' :: REGCLASS) NOT NULL,
  "name"        VARCHAR(255) COLLATE "default"                       NOT NULL,
  "description" TEXT COLLATE "default",
  "added_on"    TIMESTAMP(6),
  "updated_on"  TIMESTAMP(6),
  CONSTRAINT "auth_role_pkey" PRIMARY KEY ("role_id"),
  CONSTRAINT "uniq_name" UNIQUE ("name")
);

CREATE TABLE "auth_permission" (
  "permission_id" INT4 DEFAULT nextval('auth_permission_id_seq' :: REGCLASS) NOT NULL,
  "name"          VARCHAR(32) COLLATE "default"                              NOT NULL,
  "description"   TEXT COLLATE "default",
  "added_on"      TIMESTAMP(6),
  "updated_on"    TIMESTAMP(6),
  CONSTRAINT "auth_permission_pkey" PRIMARY KEY ("permission_id"),
  CONSTRAINT "uniq_permission_name" UNIQUE ("name")
);

CREATE TABLE "auth_subject_role" (
  "subject_role_id" INT4 NOT NULL,
  "subject_id"      INT4 NOT NULL,
  "role_id"         INT4 NOT NULL,
  CONSTRAINT "auth_subject_role_pkey" PRIMARY KEY ("subject_role_id"),
  CONSTRAINT "fk_roleid" FOREIGN KEY ("role_id") REFERENCES "public"."auth_role" ("role_id") ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT "unique_subject_role" UNIQUE ("subject_id", "role_id")
);

CREATE TABLE "auth_role_permissions" (
  "role_permission_id" INT4 DEFAULT nextval('auth_role_permissions_seq' :: REGCLASS) NOT NULL,
  "role_id"            INT4                                                          NOT NULL,
  "permission_id"      INT4                                                          NOT NULL,
  "added_on"           TIMESTAMP(6),
  CONSTRAINT "auth_role_permissions_pkey" PRIMARY KEY ("role_permission_id"),
  CONSTRAINT "fk_role_id" FOREIGN KEY ("role_id") REFERENCES "public"."auth_role" ("role_id") ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT "fk_permission_id" FOREIGN KEY ("permission_id") REFERENCES "public"."auth_permission" ("permission_id") ON DELETE CASCADE ON UPDATE CASCADE
);