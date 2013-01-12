<?php

class ControllerBase extends Phalcon\Mvc\Controller
{
	protected $currentUser = null;

    protected function initialize()
    {
    	$this->currentUser = User::findFirst('id = "' . $this->session->get('user_id') . '"');
    	$this->view->setVar('AppName', Config::getValue('core/name'));
    	$this->view->setVar('controller', $this->dispatcher->getControllerName());
    	$this->view->setVar('action', $this->dispatcher->getActionName());
    	$this->view->setVar('url_params', '');
    	$this->view->setVar("body_id", null);
    	$this->view->setVar("body_class", null);

        if ($this->currentUser) {
            $this->view->setVar("allProjects", $this->currentUser->getAllProjects());
        }
    }
}
