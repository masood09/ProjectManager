<?php

class AdminController extends ControllerBase
{
	public function indexAction()
	{
		Phalcon\Tag::setTitle('Administration');
		$this->view->setVar('developers', User::getAllDevelopers());
		$this->view->setVar('holidays', Holiday::getFutureHolidays());
	}

	public function configpostAction()
	{
		if ($this->request->isPost()) {
			$config = $this->request->getPost('config');
			$controller = $this->request->getPost('controller');
			$action = $this->request->getPost('action');

			foreach ($config as $key => $value) {
				Config::setValue($key, $value);
			}

			$this->response->redirect($controller . '/' . $action);
			$this->view->disable();
			return;
		}

		$this->response->redirect('project/index');
		$this->view->disable();
		return;
	}
}
