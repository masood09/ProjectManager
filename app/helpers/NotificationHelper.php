<?php

class NotificationHelper
{
	static function markProjectRead($user_id, $project_id)
	{
		$notifications = Notification::find('user_id = "' . $user_id . '" AND project_id = "' . $project_id . '" AND task_id IS NULL AND comment_id IS NULL AND note_id IS NULL AND upload_id IS NULL');

		foreach ($notifications AS $notification) {
			$notification->read = 1;
			$notification->save();
		}
	}

	static function markTaskRead($user_id, $project_id, $task_id)
	{
		$notifications = Notification::find('user_id = "' . $user_id . '" AND project_id = "' . $project_id . '" AND task_id = "' . $task_id . '"');

		foreach ($notifications AS $notification) {
			$notification->read = 1;
			$notification->save();
		}
	}
}
