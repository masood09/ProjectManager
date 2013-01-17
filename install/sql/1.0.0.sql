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

DROP TABLE role;

ALTER TABLE `attendance` DROP `total`;
ALTER TABLE `task` DROP `description`;

ALTER TABLE `task` ADD `hours_spent` TIME NULL DEFAULT NULL AFTER `hours`;
ALTER TABLE `task` ADD `closed_by` INT(10) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `task` ADD `comments` INT(10) UNSIGNED NULL DEFAULT NULL AFTER `assigned_to`;

CREATE TABLE IF NOT EXISTS `notification` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` int(10) unsigned NOT NULL,
    `message` TEXT NOT NULL,
    `project_id` int(10) unsigned NULL,
    `task_id` int(10) unsigned NULL,
    `comment_id` int(10) unsigned NULL,
    `note_id` int(10) unsigned NULL,
    `upload_id` int(10) unsigned NULL,
    `read` tinyint(1) NOT NULL DEFAULT '0',
    `created_by` int(10) unsigned NOT NULL,
    `created_at` datetime NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
