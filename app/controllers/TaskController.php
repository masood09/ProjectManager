<?php

class TaskController extends ControllerBase
{
	public function savepostAction()
	{
		if ($this->request->isPost()) {
			$projectId = $this->request->getPost('project_id');
			$job_id = $this->request->getPost('job_id');
			$title = $this->request->getPost('title');
			$description = $this->request->getPost('description');
			$hours = $this->request->getPost('hours');
			$created_by = $this->currentUser->id;
			$created_at = new Phalcon\Db\RawValue('now()');
			$assigned_to = $this->request->getPost('assigned_to');
			$task_id = $this->request->getPost('task_id');
			$status = 0;

			$project = Project::findFirst('id="' . $projectId . '"');

			if (!$project) {
				$this->response->redirect('project/index');
				$this->view->disable();
				return;
			}

			if (!$project->isInProject($this->currentUser)) {
				$this->response->redirect('project/index');
				$this->view->disable();
				return;
			}

			$controller = $this->request->getPost('controller');
			$action = $this->request->getPost('action');

			if (!is_null($task_id)) {
				// Editing the task.
				$task = Task::findFirst('id="' . $task_id . '"');

				if (!$task) {
					$this->response->redirect('project/index');
					$this->view->disable();
					return;
				}
			}
			else {
				$task = new Task();
				$task->created_by = $created_by;
				$task->created_at = $created_at;
			}

			$task->job_id = $job_id;
			$task->title = $title;
			$task->description = $description;
			$task->project_id = $projectId;
			$task->assigned_to = $assigned_to;
			$task->hours = $hours;
			$task->status = $status;

			if ($task->save() == true) {
				$taskUser = TaskUser::findFirst('task_id="' . $task->id . '" AND user_id="' . $created_by . '"');

				if (!$taskUser) {
					$taskUser = new TaskUser();
					$taskUser->user_id = $created_by;
					$taskUser->task_id = $task->id;
					$taskUser->created_at = new Phalcon\Db\RawValue('now()');

					if ($taskUser->save() != true) {
						$task->delete();

						foreach ($taskUser->getMessages() as $message) {
							$this->flashSession->error((string) $message);
        				}

        				$this->response->redirect($controller . '/' . $action);
        				$this->view->disable();
						return;
					}

					$this->Email->sendAssignedEmail($task);
				}

				$taskUser = null;
				$taskUser = TaskUser::findFirst('task_id="' . $task->id . '" AND user_id="' . $assigned_to . '"');

				if (!$taskUser) {
					$taskUser = new TaskUser();
					$taskUser->user_id = $assigned_to;
					$taskUser->task_id = $task->id;
					$taskUser->created_at = new Phalcon\Db\RawValue('now()');

					if ($taskUser->save() != true) {
						$task->delete();

						foreach ($taskUser->getMessages() as $message) {
							$this->flashSession->error((string) $message);
        				}

        				$this->response->redirect($controller . '/' . $action);
        				$this->view->disable();
						return;
					}
				}

				$this->flashSession->success('Task successfully saved.');

				$this->response->redirect($controller . '/' . $action);
				$this->view->disable();
				return;
			}

			foreach ($task->getMessages() as $message) {
				$this->flashSession->error((string) $message);
        	}

        	$this->response->redirect($controller . '/' . $action);
        	$this->view->disable();
			return;
		}

		$this->response->redirect('project/index');
		$this->view->disable();
		return;
	}

	public function viewAction($id=null)
	{
		$task = Task::findFirst('id="' . $id . '"');

		if (!$task) {
			$this->response->redirect('project/index');
			$this->view->disable();
			return;
		}

		$project = $task->getProject();

		if (!$project) {
			$this->response->redirect('project/index');
			$this->view->disable();
			return;
		}

		if (!$project->isInProject($this->currentUser)) {
			$this->response->redirect('project/index');
			$this->view->disable();
			return;
		}

		if (!$task) {
			$this->response->redirect('project/index');
			$this->view->disable();
			return;
		}

		$att_users = Attendance::find('task_id = ' . $id);
		$users = array();

		foreach($att_users AS $att_user) {
			$users[$att_user->user_id] = $att_user->user_id;
		}

		$task_user_time = array();
		$task_total_time = '00:00:00';

		$results = $this->modelsManager->executeQuery('SELECT SEC_TO_TIME( SUM( TIME_TO_SEC( Attendance.total ) ) ) AS task_time FROM Attendance WHERE task_id = ' . $id);

		foreach ($results AS $result) {
			if (!is_null($result->task_time)) {
				$task_total_time = $result->task_time;
				break;
			}
		}

		foreach ($users AS $user_id) {
			$results = $this->modelsManager->executeQuery('SELECT SEC_TO_TIME( SUM( TIME_TO_SEC( Attendance.total ) ) ) AS user_time FROM Attendance WHERE user_id = "' . $user_id . '" AND task_id = ' . $id);

			$user_time = '00:00:00';

			foreach ($results AS $result) {
				if (!is_null($result->user_time)) {
					$user_time = $result->user_time;
					break;
				}
			}

			$_user = User::findFirst('id = ' . $user_id);

			if ($_user) {
				$task_user_time[$_user->full_name] = $user_time;
			}
		}

		$this->view->setVar('task', $task);
		$this->view->setVar('task_user_time', $task_user_time);
		$this->view->setVar('task_total_time', $task_total_time);
		$this->view->setVar('extra_params', '/' . $id . '/');
		Phalcon\Tag::setTitle(($task->job_id) ? $task->job_id . ' - ' . $task->title : $task->title);
	}

	public function subscribeAction($task_id, $user_id)
	{
		$task = Task::findFirst('id="' . $task_id . '"');

		if (!$task) {
			$this->response->redirect('project/index');
			$this->view->disable();
			return;
		}

		$project = $task->getProject();

		if (!$project->isInProject($this->currentUser)) {
			$this->response->redirect('project/index');
			$this->view->disable();
			return;
		}

		// Let's check if the user is already subscribed.
		$taskUser = TaskUser::findFirst('task_id="' . $task_id . '" AND user_id="' . $user_id . '"');

		if ($taskUser) {
			$this->flashSession->warning('You are already subscribed to this task.');
			$this->response->redirect('task/view/' . $task_id);
			$this->view->disable();
			return;
		}

		$taskUser = new TaskUser();
		$taskUser->user_id = $user_id;
		$taskUser->task_id = $task_id;
		$taskUser->created_at = new Phalcon\Db\RawValue('now()');

		$taskUser->save();

		$this->flashSession->success('You have successfully subscribed to this task.');
		$this->response->redirect('task/view/' . $task_id);
		$this->view->disable();
		return;
	}

	public function unsubscribeAction($task_id, $user_id)
	{
		$task = Task::findFirst('id="' . $task_id . '"');

		if (!$task) {
			$this->response->redirect('project/index');
			$this->view->disable();
			return;
		}

		$project = $task->getProject();

		if (!$project->isInProject($this->currentUser)) {
			$this->response->redirect('project/index');
			$this->view->disable();
			return;
		}

		// Let's check if the user is already subscribed.
		$taskUser = TaskUser::findFirst('task_id="' . $task_id . '" AND user_id="' . $user_id . '"');

		if ($taskUser) {
			// User already subscribed.
			$taskUser->delete();
			$this->flashSession->success('You have successfully unsubscribed from this task.');
		}
		else {
			$this->flashSession->warning('You are not subscibed to this task.');
		}

		$this->response->redirect('task/view/' . $task_id);
		$this->view->disable();
		return;
	}

	public function addcommentAction()
	{
		if ($this->request->isPost()) {
			$task_id = $this->request->getPost('task_id');
			$user_id = $this->currentUser->id;
			$message = $this->request->getPost('comment');
			$created_at = new Phalcon\Db\RawValue('now()');

			$task = Task::findFirst('id="' . $task_id . '"');

			if (!$task) {
				$this->response->redirect('project/index');
				$this->view->disable();
				return;
			}

			$task = Task::findFirst('id="' . $task_id . '"');

			$project = $task->getProject();

			if (!$project->isInProject($this->currentUser)) {
				$this->response->redirect('project/index');
				$this->view->disable();
				return;
			}

			$task_status = $this->request->getPost('task_complete');

			if ($task_status) {
				$completed_date = new Phalcon\Db\RawValue('CURDATE()');
				$task->completed_on = $completed_date;
				$task_status = 1;
				$this->Email->sendTaskClosedEmail($task);
			}
			else {
				$task_status = 0;
			}

			$comment = new Comment();
			$comment->user_id = $user_id;
			$comment->task_id = $task_id;
			$comment->comment = $message;
			$comment->created_at = $created_at;

			if ($comment->save() == true) {
				$this->Email->sendCommentEmail($comment);

				$this->flashSession->success('Comment posted successfully.');

				$task->status = $task_status;
				$task->save();

				$taskUser = TaskUser::findFirst('user_id="' . $user_id . '" AND task_id="' . $task_id . '"');

				if (!$taskUser) {
					$taskUser = new TaskUser();
					$taskUser->user_id = $user_id;
					$taskUser->task_id = $task_id;
					$taskUser->created_at = new Phalcon\Db\RawValue('now()');

					$taskUser->save();
				}

				$this->response->redirect('task/view/' . $task_id);
				$this->view->disable();
				return;
			}

			foreach ($comment->getMessages() as $message) {
				$this->flashSession->error((string) $message);
        	}

        	$this->response->redirect('task/view/' . $task_id);
        	$this->view->disable();
			return;
		}

		$this->response->redirect('project/index');
		$this->view->disable();
		return;
	}

	public function indexAction()
	{
		Phalcon\Tag::appendTitle('Your Tasks');
		$this->view->setVar('tasks', $this->currentUser->getAllTasks());
	}
}
