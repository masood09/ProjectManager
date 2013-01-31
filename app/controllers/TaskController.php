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

class TaskController extends ControllerBase
{
    public function updateajaxAction()
    {
        if ($this->request->isPost()) {
            $task_id = $this->request->getPost('pk');

            $task = Task::findFirst('id = "' . $task_id . '"');

            if (!$task) {
                $this->view->disable();
                return;
            }

            if (!$task->getProject()->isInProject($this->currentUser)) {
                $this->view->disable();
                return;
            }

            $data_name = $this->request->getPost('name');
            $value = $this->request->getPost('value');

            if ($data_name == 'title') {
                $task->title = $value;
            }

            if ($data_name == 'job_id') {
                $task->job_id = $value;
            }

            if ($data_name == 'assigned_to') {
                $assignedUser = User::findFirst('id = "' . $value . '"');

                if ($assignedUser) {
                    if ($task->getProject()->isInProject($assignedUser)) {
                        $task->assigned_to = $value;

                        $taskUser = TaskUser::findFirst('user_id="' . $assignedUser->id . '" AND task_id="' . $task->id . '"');

                        if (!$taskUser) {
                            $taskUser = new TaskUser();
                            $taskUser->user_id = $assignedUser->id;
                            $taskUser->task_id = $task->id;
                            $taskUser->created_at = new Phalcon\Db\RawValue('now()');

                            $taskUser->save();
                        }

                        if ($value != $this->currentUser->id) {
                            NotificationHelper::taskAssignedNotification($task->getProject(), $task, $this->currentUser);
                        }
                    }
                }
            }

            if ($data_name == 'hours') {
                $task->hours = $value . ':00:00';
            }

            if ($data_name == 'status') {
                if ($value == 1 && $task->status == 0) {
                    NotificationHelper::taskClosedNotification($task->getProject(), $task, $this->currentUser);
                }
                else if ($value == 0 && $task->status == 1) {
                    NotificationHelper::taskReOpenedNotification($task->getProject(), $task, $this->currentUser);
                }

                $task->status = $value;

                if ($task->status == 1) {
                    $task->completed_on = new Phalcon\Db\RawValue('CURDATE()');
                    $task->closed_by = $this->currentUser->id;
                }
                else {
                    $task->completed_on = null;
                    $task->closed_by = null;
                }
            }

            $task->save();
        }

        $this->view->disable();
        return;
    }

    public function updatecommentajaxAction()
    {
        if ($this->request->isPost()) {
            $comment_id = $this->request->getPost('pk');

            $comment = Comment::findFirst('id = "' . $comment_id . '"');

            if (!$comment) {
                $this->view->disable();
                return;
            }

            if (!$comment->getTask()->getProject()->isInProject($this->currentUser)) {
                $this->view->disable();
                return;
            }

            if ($comment->user_id != $this->currentUser->id) {
                $this->view->disable();
                return;
            }

            $data_name = $this->request->getPost('name');
            $value = $this->request->getPost('value');

            if ($data_name == 'comment') {
                $comment->comment = $value;
                $comment->save();

                NotificationHelper::updateCommentNotification(
                    $comment->getTask()->getProject(),
                    $comment->getTask(),
                    $comment
                );
            }

            $this->view->disable();
            return;
        }

        $this->view->disable();
        return;
    }

    public function postcommentAction()
    {
        if ($this->request->isPost()) {
            $task_id = $this->request->getPost('task_id');
            $task = Task::findFirst('id = "' . $task_id . '"');

            if (!$task) {
                $this->response->redirect('dashboard/index/');
                $this->view->disable();
                return;
            }

            if (!$task->getProject()->isInProject($this->currentUser)) {
                $this->response->redirect('dashboard/index/');
                $this->view->disable();
                return;
            }

            $message = $this->request->getPost('comment');

            $controller = $this->request->getPost('controller');
            $action = $this->request->getPost('action');

            if (!$controller || !$action) {
                $controller = 'dashboard';
                $action = 'index';
            }

            if (!$message || $message == '') {
                $this->flashSession->error('Comment should be specified');
                $this->response->redirect($controller . '/' . $action);
                $this->view->disable();
                return;
            }

            $comment = new Comment();
            $comment->user_id = $this->currentUser->id;
            $comment->task_id = $task->id;
            $comment->comment = $message;
            $comment->created_at = new Phalcon\Db\RawValue('now()');

            if ($comment->save() != true) {
                foreach ($comment->getMessages() as $message) {
                    $this->flashSession->error((string) $message);
                }

                $this->response->redirect($controller . '/' . $action);
                $this->view->disable();
                return;
            }

            $taskUser = TaskUser::findFirst('user_id="' . $this->currentUser->id . '" AND task_id="' . $task->id . '"');

            if (!$taskUser) {
                $taskUser = new TaskUser();
                $taskUser->user_id = $this->currentUser->id;
                $taskUser->task_id = $task->id;
                $taskUser->created_at = new Phalcon\Db\RawValue('now()');

                $taskUser->save();
            }

            $task->comments += 1;
            $task->save();

            if ($this->request->hasFiles() == true) {
                foreach ($this->request->getUploadedFiles() as $file) {
                    if (!$file->getName() || !$file->getTempName() || $file->getSize() == 0) {
                        continue;
                    }

                    $this->uploadHelper->uploadFile($this->currentUser->id, $file, $task->getProject()->id, $task->id, $comment->id);
                }
            }

            NotificationHelper::newCommentNotification(
                $task->getProject(),
                $task,
                $comment
            );

            $this->flashSession->success('Comment posted successfully');

            $this->response->redirect($controller . '/' . $action . '#comment-' . $comment->id);
            $this->view->disable();
            return;
        }

        $this->response->redirect('dashboard/index/');
        $this->view->disable();
        return;
    }

    public function subscribeajaxAction($project_id, $task_id, $user_id)
    {
    if (is_null($project_id)) {
            $this->response->redirect('dashboard/index');
            $this->view->disable();
            return;
        }

        if (is_null($task_id)) {
            $this->response->redirect('dashboard/index');
            $this->view->disable();
            return;
        }

        if (is_null($user_id)) {
            $this->response->redirect('dashboard/index');
            $this->view->disable();
            return;
        }

        $project = Project::findFirst('id = "' . $project_id . '"');

        if (!$project) {
            $this->response->redirect('dashboard/index');
            $this->view->disable();
            return;
        }

        if (!$project->isInProject($this->currentUser)) {
            $this->response->redirect('dashboard/index');
            $this->view->disable();
            return;
        }

        $user = User::findFirst('id = "' . $user_id . '"');

        if (!$user) {
            $this->response->redirect('dashboard/index');
            $this->view->disable();
            return;
        }

        if (!$project->isInProject($user)) {
            $this->response->redirect('dashboard/index');
            $this->view->disable();
            return;
        }

        $task = Task::findFirst('id = "' . $task_id . '"');

        if (!$task) {
            $this->response->redirect('dashboard/index');
            $this->view->disable();
            return;
        }

        if ($task->assigned_to == $user->id) {
            $return['subscribed'] = true;
            echo json_encode($return);
            $this->view->disable();
            return;
        }

        $taskUser = TaskUser::findFirst('user_id="' . $user->id . '" AND task_id="' . $task->id . '"');

        if (!$taskUser) {
            $taskUser = new TaskUser();
            $taskUser->user_id = $user->id;
            $taskUser->task_id = $task->id;
            $taskUser->created_at = new Phalcon\Db\RawValue('now()');

            $taskUser->save();

            $return['subscribed'] = true;
            echo json_encode($return);
            $this->view->disable();
            return;
        }
        else {
            $taskUser->delete();
            $return['subscribed'] = false;
            echo json_encode($return);
            $this->view->disable();
            return;
        }

        $this->response->redirect('dashboard/index');
        $this->view->disable();
        return;
    }
}
