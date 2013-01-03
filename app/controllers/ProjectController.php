<?php

class ProjectController extends ControllerBase
{
	protected function _canCreateProject()
	{
		if ($this->currentUser) {
			$role_id = $this->currentUser->role_id;

			if ($role_id == 1) {
				return true;
			}
		}

		return false;
	}

	public function indexAction()
	{
		Phalcon\Tag::setTitle('Your Projects');

		if (!is_null($this->currentUser)) {
			$this->view->setVar('allProjects', $this->currentUser->getAllProjects());
		}

		$this->view->setVar('canCreateProject', $this->_canCreateProject());
		$this->view->setVar('developers', User::getAllDevelopers(true));
	}

	public function savepostAction()
	{
		if (!$this->_canCreateProject()) {
			$this->response->redirect('project/index');
			$this->view->disable();
			return;
		}

		if ($this->request->isPost()) {
			$projectId = $this->request->getPost('project_id');
			$controller = $this->request->getPost('controller');
			$action = $this->request->getPost('action');
			$name = $this->request->getPost('name');
			$description = $this->request->getPost('description');

			$project = Project::findFirst('id="' . $projectId . '"');

			if ($project) {
				if ($this->currentUser->role_id != 1 && $this->currentUser->id != $project->created_by) {
					$this->response->redirect('project/index');
					$this->view->disable();
					return;
				}

				$project->name = $name;
				$project->description = $description;

				if ($project->save() == true) {
					$this->flashSession->success('Project saved successfully.');

					$this->response->redirect($controller . '/' . $action);
					$this->view->disable();
					return;
				}

				foreach ($project->getMessages() as $message) {
					$this->flashSession->error((string) $message);
            	}

            	$this->response->redirect($controller . '/' . $action);
            	$this->view->disable();
				return;
			}

			$this->flashSession->error('Project does not exist.');
			$this->response->redirect('project/index');
			$this->view->disable();
			return;
		}

		$this->response->redirect('project/index');
		$this->view->disable();
		return;
	}

	public function createpostAction()
	{
		if ($this->request->isPost()) {
			$name = $this->request->getPost('name');
			$description = $this->request->getPost('description');
			$controller = $this->request->getPost('controller', 'string');
			$action = $this->request->getPost('action', 'string');

			$project = new Project();
			$projectUser = new ProjectUser();
			$project->name = $name;
			$project->description = $description;
			$project->created_by = $this->currentUser->id;
			$project->created_at = new Phalcon\Db\RawValue('now()');
			$project->status = 1;

			if ($project->save() == true) {
				$projectUser->user_id = $this->currentUser->id;
				$projectUser->project_id = $project->id;
				$projectUser->created_at = new Phalcon\Db\RawValue('now()');

				if ($projectUser->save() == true) {
					$this->flashSession->success('Project successfully created.');

					$this->response->redirect($controller . '/' . $action);
					$this->view->disable();
					return;
				}
				else {
					$project->delete();
				}

				// Let's add all admin users to this project.
				$adminUsers = User::find('role_id="' . 1 . '"');

				foreach($adminUsers AS $adminUser) {
					// Let's check whether the user is already present in the project.
					$projectUser = null;
					$projectUser = ProjectUser::findFirst('project_id="' . $project->id . '" AND user_id="' . $adminUser->id . '"');

					if (!$projectUser) {
						$projectUser = new ProjectUser();
						$projectUser->user_id = $adminUser->id;
						$projectUser->project_id = $project->id;
						$projectUser->created_at = new Phalcon\Db\RawValue('now()');

						$projectUser->save();
					}
				}
			}

			foreach ($project->getMessages() as $message) {
				$this->flashSession->error((string) $message);
            }

            foreach ($projectUser->getMessages() as $message) {
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

	public function saveusersAction()
	{
		if ($this->request->isPost()) {
			$controller = $this->request->getPost('controller', 'string');
			$action = $this->request->getPost('action', 'string');
			$projectId = $this->request->getPost('project_id');
			$developers = $this->request->getPost('developers');

			$project = Project::findFirst('id="' . $projectId . '"');

			if ($project) {
				$users = array();
				$users[] = $project->created_by;

				if (count($developers) > 0) {
					foreach ($developers as $developer) {
						$users[] = $developer;
					}
				}

				$users[] = $project->created_by;

				$projectUsers = ProjectUser::find('project_id="' . $projectId . '"');

				foreach($projectUsers AS $projectUser) {
					if (!in_array($projectUser->user_id, $users)) {
						$projectUser->delete();
					}
				}

				foreach ($users as $user) {
					$projectUser = ProjectUser::findFirst('project_id="' . $projectId . '" AND user_id="' . $user . '"');

					if ($projectUser) {
						continue;
					}

					$projectUser = new ProjectUser();
					$projectUser->user_id = $user;
					$projectUser->project_id = $projectId;
					$projectUser->created_at = new Phalcon\Db\RawValue('now()');

					$projectUser->save();
				}

				$this->flashSession->success('Users for ' . $project->name . ' successfully saved.');

				$this->response->redirect($controller . '/' . $action);
				$this->view->disable();
				return;
			}
		}

		$this->response->redirect('project/index');
		$this->view->disable();
		return;
	}

	public function viewAction($id=null)
	{
		if (is_null($id)) {
			$this->response->redirect('project/index');
			$this->view->disable();
			return;
		}

		$project = Project::findFirst('id="' . $id . '"');

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

		$this->view->setVar('project', $project);
		$this->view->setVar('developers', User::getAllDevelopers(true));

		Phalcon\Tag::setTitle($project->name);
	}
}
