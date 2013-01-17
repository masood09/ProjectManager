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
