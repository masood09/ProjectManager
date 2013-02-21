<?php
// Copyright (C) 2013 Masood Ahmed

// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.

// You should have received a copy of the GNU General Public License
// along with this program. If not, see <http://www.gnu.org/licenses/>.

class NotificationHelper
{
    static function markProjectRead($user_id, $project_id)
    {
	$notifications = Notification::find('user_id = "' . $user_id . '" AND type = "project" AND type_id = "' . $project_id . '"');

        foreach ($notifications AS $notification) {
            $notification->read = 1;
            $notification->save();
        }
    }

    static function markTaskRead($user_id, $task_id)
    {
	$notifications = Notification::find('user_id = "' . $user_id . '" AND type = "task" AND type_id = "' . $task_id . '"');

        foreach ($notifications AS $notification) {
            $notification->read = 1;
            $notification->save();
        }
    }

    static function markCommentRead($user_id, $comment_id)
    {
	$notifications = Notification::find('user_id = "' . $user_id . '" AND type = "comment" AND type_id = "' . $comment_id . '"');

	foreach ($notifications AS $notification) {
	    $notification->read = 1;
	    $notification->save();
	}
    }

    static function markNoteRead($user_id, $note_id)
    {
	$notifications = Notification::find('user_id = "' . $user_id . '" AND type = "note" AND type_id = "' . $note_id . '"');

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
		$notification->type = 'task';
		$notification->type_id = $task->id;
                $notification->message = '<strong>' . $user->full_name . '</strong> closed the task <strong>' . $task->title . '</strong> in project <strong> ' . $project->name . '</strong>';
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
		$notification->type = 'task';
		$notification->type_id = $task->id;
                $notification->message = '<strong>' . $user->full_name . '</strong> re-opened the task <strong>' . $task->title . '</strong> in project <strong> ' . $project->name . '</strong>';
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
	$notification->type = 'task';
	$notification->type_id = $task->id;
        $notification->message = '<strong>' . $user->full_name . '</strong> has assigned the task <strong>' . $task->title . '</strong> of the project <strong>' . $project->name . '</strong> to you';
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
		$notification->type = 'comment';
		$notification->type_id = $comment->id;
                $notification->message = '<strong>' . $commentUser->full_name . '</strong> updated comment on your task <strong>' . $task->title . '</strong> : "' . substr(strip_tags($comment->comment), 0, 200) . '..."';
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
		$notification->type = 'comment';
		$notification->type_id = $comment->id;
                $notification->message = '<strong>' . $commentUser->full_name . '</strong> has posted a new comment on your task <strong>' . $task->title . '</strong> : "' . substr(strip_tags($comment->comment), 0, 200) . '..."';
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
	    $notification->type = 'project';
	    $notification->type_id = $project->id;
            $notification->message = '<strong>' . $user->full_name . '</strong> has created a new project <strong>' . $project->name . '</strong>';
            $notification->read = 0;
            $notification->created_by = $user->id;
            $notification->created_at = new Phalcon\Db\RawValue('now()');

            $notification->save();
        }
    }

    static function newNoteNotification($project, $note)
    {
        $projectUsers = ProjectUser::find('project_id = "' . $project->id . '" AND user_id != "' . $note->user_id . '"');

        foreach ($projectUsers AS $projectUser) {
            $notification = new Notification();

            $notification->user_id = $projectUser->user_id;
	    $notification->type = 'notes';
	    $notification->type_id = $note->id;
            $notification->message = '<strong>' . $note->getUser()->full_name . '</strong> has created a new note <strong>' . $note->title . '</strong> for project <strong>' . $project->name . '</strong>';
            $notification->read = 0;
            $notification->created_by = $note->user_id;
            $notification->created_at = new Phalcon\Db\RawValue('now()');

            $notification->save();
        }
    }
}
