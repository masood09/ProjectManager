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

class NotificationController extends ControllerBase
{
    public function gotoAction($id=null)
    {
        if (is_null($id)) {
            $this->response->redirect('dashboard/index');
            $this->view->disable();
            return;
        }

        $notification = Notification::findFirst('id = "' . $id . '"');

        if (!$notification) {
            $this->response->redirect('dashboard/index');
            $this->view->disable();
            return;
        }

        $type = $notification->type;
        $type_id = $notification->type_id;

        if ($type == 'project') {
            $project = Project::findFirst('id = "' . $type_id . '"');

            if (!$project) {
            $this->response->redirect('dashboard/index');
            $this->view->disable();
            return;
            }

            NotificationHelper::markProjectRead($this->currentUser->id, $project->id);

            $this->response->redirect('project/view/' . $project->id);
            $this->view->disable();
            return;
        }

        if ($type == 'task') {
            $task = Task::findFirst('id = "' . $type_id . '"');

            if (!$task) {
            $this->response->redirect('dashboard/index');
            $this->view->disable();
            return;
            }

            NotificationHelper::markTaskRead($this->currentUser->id, $task->id);

            $this->response->redirect('project/view/' . $task->project_id . '/' . $task->id);
            $this->view->disable();
            return;
        }

        if ($type == 'comment') {
            $comment = Comment::findFirst('id = "' . $type_id . '"');

            if (!$comment) {
            $this->response->redirect('dashboard/index');
            $this->view->disable();
            return;
            }

            $task = $comment->getTask();

            NotificationHelper::markCommentRead($this->currentUser->id, $comment->id);

            $this->response->redirect('project/view/' . $task->project_id . '/' . $task->id . '#comment-' . $comment->id);
            $this->view->disable();
            return;
        }

        if ($type == 'note') {
            $note = Note::findFirst('id = "' . $type_id . '"');

            if (!$note) {
            $this->response->redirect('dashboard/index');
            $this->view->disable();
            return;
            }

            NotificationHelper::markNoteRead($this->currentUser->id, $note->id);

            $this->response->redirect('project/notes/' . $note->project_id . '/' . $note->id);
            $this->view->disable();
            return;
        }
    }
}
