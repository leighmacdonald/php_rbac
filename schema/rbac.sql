CREATE TABLE `auth_permission` (
    `permission_id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
    `name`  varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
    `description`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
    `added_on`  datetime NULL DEFAULT NULL ,
    `updated_on`  datetime NULL DEFAULT NULL ,
    PRIMARY KEY (`permission_id`),
    UNIQUE INDEX `uniq_perm` USING BTREE (`name`)
) ENGINE=InnoDB;

CREATE TABLE `auth_role` (
    `role_id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
    `name`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
    `description`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
    `added_on`  datetime NULL DEFAULT NULL ,
    `updated_on`  datetime NULL DEFAULT NULL ,
    PRIMARY KEY (`role_id`),
    UNIQUE INDEX `uniq_name` USING BTREE (`name`)
) ENGINE=InnoDB;

CREATE TABLE `auth_role_permission` (
    `role_permission_id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
    `role_id`  int(10) UNSIGNED NOT NULL ,
    `permission_id`  int(10) UNSIGNED NOT NULL ,
    `added_on`  datetime NULL DEFAULT NULL ,
    PRIMARY KEY (`role_permission_id`),
    FOREIGN KEY (`permission_id`) REFERENCES `auth_permission` (`permission_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`role_id`) REFERENCES `auth_role` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `fk_role` USING BTREE (`role_id`) ,
    INDEX `fk_permission` USING BTREE (`permission_id`)
) ENGINE=InnoDB;

CREATE TABLE `auth_user_role` (
    `user_role_id`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
    `user_id`  int(10) UNSIGNED NOT NULL ,
    `role_id`  int(10) UNSIGNED NOT NULL ,
    PRIMARY KEY (`user_role_id`),
    FOREIGN KEY (`role_id`) REFERENCES `auth_role` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE INDEX `role_id` USING BTREE (`role_id`, `user_id`) ,
    INDEX `fk_userid` USING BTREE (`user_id`) ,
    INDEX `fk_roleid` USING BTREE (`role_id`)
) ENGINE=InnoDB;
