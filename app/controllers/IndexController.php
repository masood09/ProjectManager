<?php

class IndexController extends ControllerBase
{
	public function indexAction()
	{
		if ($this->currentUser == null) {
			$this->response->redirect('user/login');
			$this->view->disable();
			return;
		}

		$this->response->redirect('project/index');
		$this->view->disable();
		return;
	}
}
