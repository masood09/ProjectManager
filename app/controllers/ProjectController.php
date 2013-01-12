<?php

class ProjectController extends ControllerBase
{
	public function viewAction($id=null, $task_id=null)
	{
		if (is_null($id)) {
			$this->response->redirect('dashboard/index');
			$this->view->disable();
			return;
		}

		$project = Project::findFirst('id = "' . $id . '"');

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

		$allProjectTasks = $project->getAllTasks();

		if (count($allProjectTasks) > 0) {
			if (is_null($task_id)) {
				$currentTask = $allProjectTasks[0];
			}
			else {
				$currentTask = Task::findFirst('id = "' . $task_id . '"');

				if (!$currentTask) {
					$this->response->redirect('project/view/' . $project->id);
					$this->view->disable();
					return;
				}
			}
		}

		$this->view->setVar('currentProject', $project);
		$this->view->setVar('allProjectTasks', $allProjectTasks);
		$this->view->setVar('currentTask', $currentTask);

		Phalcon\Tag::setTitle($project->name . ' | ' . $currentTask->title);
	}
}
