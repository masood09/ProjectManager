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

	static function taskClosedNotification($project, $task, $user)
	{
		foreach ($task->getTaskUser() AS $taskUser) {
			if ($taskUser->user_id != $user->id) {
				$notification = new Notification();

				$notification->user_id = $taskUser->user_id;
				$notification->message = '<strong>' . $user->full_name . '</strong> closed the task <strong>' . $task->title . '</strong> in project <strong> ' . $project->name . '</strong>';
				$notification->project_id = $task->project_id;
				$notification->task_id = $task->id;
				$notification->read = 0;
				$notification->created_by = $user->id;
				$notification->created_at = new Phalcon\Db\RawValue('now()');

				$notification->save();
			}
		}
	}

	static function taskReOpenedNotification($project, $task, $user)
	{
		foreach ($task->getTaskUser() AS $taskUser) {
			if ($taskUser->user_id != $user->id) {
				$notification = new Notification();

				$notification->user_id = $taskUser->user_id;
				$notification->message = '<strong>' . $user->full_name . '</strong> re-opened the task <strong>' . $task->title . '</strong> in project <strong> ' . $project->name . '</strong>';
				$notification->project_id = $task->project_id;
				$notification->task_id = $task->id;
				$notification->read = 0;
				$notification->created_by = $user->id;
				$notification->created_at = new Phalcon\Db\RawValue('now()');

				$notification->save();
			}
		}
	}

	static function taskAssignedNotification($project, $task, $user)
	{
		$notification = new Notification();

		$notification->user_id = $task->assigned_to;
		$notification->message = '<strong>' . $user->full_name . '</strong> has assigned the task <strong>' . $task->title . '</strong> of the project <strong>' . $project->name . '</strong> to you';
		$notification->project_id = $project->id;
		$notification->task_id = $task->id;
		$notification->read = 0;
		$notification->created_by = $user->id;
		$notification->created_at = new Phalcon\Db\RawValue('now()');

		$notification->save();
	}

	static function updateCommentNotification($project, $task, $comment)
	{
		$commentUser = $comment->getUser();

		foreach ($task->getTaskUser() AS $taskUser) {
			if ($comment->user_id != $taskUser->user_id) {
				$notification = new Notification();

				$notification->user_id = $taskUser->user_id;
				$notification->message = '<strong>' . $commentUser->full_name . '</strong> updated comment on your task <strong>' . $task->title . '</strong> : "' . substr(strip_tags($comment->comment), 0, 200) . '..."';
				$notification->project_id = $task->project_id;
				$notification->task_id = $comment->task_id;
				$notification->comment_id = $comment->id;
				$notification->read = 0;
				$notification->created_by = $comment->user_id;
				$notification->created_at = new Phalcon\Db\RawValue('now()');

				$notification->save();
			}
		}
	}

	static function newCommentNotification($project, $task, $comment)
	{
		$commentUser = $comment->getUser();

		foreach ($task->getTaskUser() AS $taskUser) {
			if ($comment->user_id != $taskUser->user_id) {
				$notification = new Notification();

				$notification->user_id = $taskUser->user_id;
				$notification->message = '<strong>' . $commentUser->full_name . '</strong> has posted a new comment on your task <strong>' . $task->title . '</strong> : "' . substr(strip_tags($comment->comment), 0, 200) . '..."';
				$notification->project_id = $task->project_id;
				$notification->task_id = $comment->task_id;
				$notification->comment_id = $comment->id;
				$notification->read = 0;
				$notification->created_by = $comment->user_id;
				$notification->created_at = new Phalcon\Db\RawValue('now()');

				$notification->save();
			}
		}
	}

	static function newProjectNotification($project, $user)
	{
		$projectUsers = ProjectUser::find('project_id = "' . $project->id . '" AND user_id != "' . $user->id . '"');

		foreach ($projectUsers AS $projectUser) {
			$notification = new Notification();

			$notification->user_id = $projectUser->user_id;
			$notification->message = '<strong>' . $user->full_name . '</strong> has created a new project <strong>' . $project->name . '</strong>';
			$notification->project_id = $project->id;
			$notification->read = 0;
			$notification->created_by = $user->id;
			$notification->created_at = new Phalcon\Db\RawValue('now()');

			$notification->save();
		}
	}
}
