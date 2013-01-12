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

		$this->view->setVar('currentProject', $project);
	}
}
