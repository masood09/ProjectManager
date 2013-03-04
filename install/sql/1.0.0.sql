-- Copyright (C) 2013 Masood Ahmed

-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.

-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
-- GNU General Public License for more details.

-- You should have received a copy of the GNU General Public License
-- along with this program. If not, see <http://www.gnu.org/licenses/>.

DROP TABLE `role`;
DROP TABLE `session`;

ALTER TABLE `attendance` DROP `total`;
ALTER TABLE `attendance` CHANGE `task_id` `task_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `attendance` CHANGE `start` `start` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `attendance` CHANGE `end` `end` TIMESTAMP NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `attendance` ADD `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `attendance` ADD `updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';

ALTER TABLE `comment` DROP `uuid`;
ALTER TABLE `comment` CHANGE `created_at` `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `comment` ADD `updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';

ALTER TABLE `config` CHANGE `value` `value` VARCHAR(255) NULL DEFAULT NULL;

ALTER TABLE `holiday` ADD `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `holiday` ADD `updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';

ALTER TABLE `project` CHANGE `name` `name` VARCHAR(255) NOT NULL;
ALTER TABLE `project` CHANGE `status` `status` ENUM( '0', '1' ) NULL DEFAULT '1' AFTER `created_by`;
ALTER TABLE `project` CHANGE `created_by` `created_by` INT(10) UNSIGNED NOT NULL;
ALTER TABLE `project` CHANGE `created_at` `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `project` ADD `updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';

ALTER TABLE `project_user` CHANGE `user_id` `user_id` INT(10) UNSIGNED NOT NULL AFTER `project_id`;
ALTER TABLE `project_user` CHANGE `created_at` `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `project_user` ADD `updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';

ALTER TABLE `task` DROP `description`;
ALTER TABLE `task` CHANGE `title` `title` VARCHAR(255) NOT NULL;
ALTER TABLE `task` CHANGE `job_id` `job_id` VARCHAR(20) NULL;
ALTER TABLE `task` CHANGE `project_id` `project_id` INT(10) UNSIGNED NOT NULL;
ALTER TABLE `task` CHANGE `created_by` `created_by` INT(10) UNSIGNED NOT NULL;
ALTER TABLE `task` CHANGE `assigned_to` `assigned_to` INT(10) UNSIGNED NOT NULL;
ALTER TABLE `task` ADD `hours_spent` TIME NULL DEFAULT NULL AFTER `hours`;
ALTER TABLE `task` CHANGE `status` `status` ENUM( '0', '1' ) NOT NULL DEFAULT '0';
ALTER TABLE `task` ADD `closed_by` INT(10) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `task` ADD `num_comments` INT(10) UNSIGNED NULL DEFAULT '0' AFTER `assigned_to`;
ALTER TABLE `task` CHANGE `created_at` `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `closed_by`;
ALTER TABLE `task` ADD `updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';

ALTER TABLE `task_user` CHANGE `task_id` `task_id` INT(10) UNSIGNED NOT NULL AFTER `id`;
ALTER TABLE `task_user` CHANGE `created_at` `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `task_user` ADD `updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';

ALTER TABLE `note` CHANGE `content` `content` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `note` CHANGE `created_at` `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `note` ADD `updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';

ALTER TABLE `upload` DROP `filepath`;
ALTER TABLE `upload` DROP `type`;
ALTER TABLE `upload` DROP `uuid`;
ALTER TABLE `upload` CHANGE `size` `size` INT(10) UNSIGNED NOT NULL;
ALTER TABLE `upload` CHANGE `uploaded_at` `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `upload` ADD `updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';

ALTER TABLE `user` ADD `weekoffs` VARCHAR(255) NULL DEFAULT NULL AFTER `role_id`;
ALTER TABLE `user` ADD `leaves` INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `role_id`;
ALTER TABLE `user` ADD `leaves_assigned_on` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `leaves`;
ALTER TABLE `user` CHANGE `created_at` `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `user` ADD `updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';

CREATE TABLE IF NOT EXISTS `notification` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT(10) UNSIGNED NOT NULL,
    `type` VARCHAR(10) NOT NULL,
    `type_id` INT(10) UNSIGNED NOT NULL,
    `message` VARCHAR(255) NOT NULL,
    `read` ENUM( '0', '1' ) NULL DEFAULT '0',
    `created_by` INT(10) UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
    `updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `leaves` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` int(10) unsigned NOT NULL,
    `date` date NOT NULL,
    `reason` text NULL,
    `approved` ENUM( '0', '1' ) NULL DEFAULT NULL,
    `approved_by` int(10) unsigned NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
    `updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `report_daily` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT(10) UNSIGNED NOT NULL,
    `date` DATE NOT NULL,
    `started` TIME NULL,
    `ended` TIME NULL,
    `total_hours` TIME NULL,
    `logged_hours` TIME NULL,
    `productivity` INT(10) NULL DEFAULT 0,
    `num_tasks_worked` INT(10) NULL DEFAULT 0,
    `time_on_tasks` TIME NULL,
    `avg_time_on_tasks` TIME NULL,
    `num_real_tasks_worked` INT(10) NULL DEFAULT 0,
    `time_on_real_tasks` TIME NULL,
    `avg_time_on_real_tasks` TIME NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
    `updated_at` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
