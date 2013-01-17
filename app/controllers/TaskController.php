<?php

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
            $return = array();

            $task_id = $this->request->getPost('task_id');
            $task = Task::findFirst('id = "' . $task_id . '"');

            if (!$task) {
                $this->view->disable();

                $return['success'] = false;
                echo json_encode($return);

                return;
            }

            if (!$task->getProject()->isInProject($this->currentUser)) {
                $this->view->disable();

                $return['success'] = false;
                echo json_encode($return);

                return;
            }

            $message = $this->request->getPost('comment');

            if (!$message || $message == '') {
                $this->view->disable();

                $return['success'] = false;
                echo json_encode($return);

                return;
            }

            $comment = new Comment();
            $comment->user_id = $this->currentUser->id;
            $comment->task_id = $task->id;
            $comment->comment = $message;
            $comment->created_at = new Phalcon\Db\RawValue('now()');

            if ($comment->save() != true) {
                $this->view->disable();

                $return['success'] = false;
                echo json_encode($return);

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

            // Let's increase the task comment count by 1.
            $task->comments += 1;
            $task->save();

            NotificationHelper::newCommentNotification(
                $task->getProject(),
                $task,
                $comment
            );

            $this->view->setVar('comment', Comment::findFirst('id = "' . $comment->id . '"'));
            $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
            $this->view->render('ajax', 'comment');
            $this->view->finish();

            $return['success'] = true;
            $return['comment_html'] = $this->view->getContent();
            $return['comment_html_id'] = '#comment-content-' . $comment->id;

            echo json_encode($return);
            $this->view->disable();
            return;
        }

        $this->view->disable();
        return;
    }
}
