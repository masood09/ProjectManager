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
				$task->assigned_to = $value;
			}

			if ($data_name == 'hours') {
				$task->hours = $value . ':00:00';
			}

			if ($data_name == 'status') {
				$task->status = $value;
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
	}
}
