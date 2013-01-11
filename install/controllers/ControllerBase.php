<?php

class ControllerBase extends Phalcon\Mvc\Controller
{
    protected function initialize()
    {
    	$this->view->setVar('AppName', 'Project Manager');
    	$this->view->setVar('controller', $this->dispatcher->getControllerName());
    	$this->view->setVar('action', $this->dispatcher->getActionName());
    	$this->view->setVar('url_params', '');
    	$this->view->setVar("body_id", null);
    	$this->view->setVar("body_class", null);
    }
}
