<?php

class ControllerBase extends Phalcon\Mvc\Controller
{
	protected $currentUser = null;

    protected function _checkSystem()
    {
        if (!Config::keyExists('core/data_version')) {
            // If core/data_version does not exist, we set the value to 0.1.0
            Config::setValue('core/data_version', '0.1.0');
        }

        if (!Config::keyExists('core/app_version')) {
            // If core/data_version does not exist, we set the value to 0.1.0
            Config::setValue('core/app_version', '0.1.0');
        }
    }

    protected function initialize()
    {
        $this->_checkSystem();

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
