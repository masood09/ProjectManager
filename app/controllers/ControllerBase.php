<?php

class ControllerBase extends Phalcon\Mvc\Controller
{
    protected function initialize()
    {
    	$this->view->setVar('AppName', Config::getValue('core/name'));
    	$this->view->setVar('controller', $this->dispatcher->getControllerName());
    	$this->view->setVar('action', $this->dispatcher->getActionName());
    	$this->view->setVar('url_params', '');
    	$this->view->setVar("body_id", 'defaultId');
    	$this->view->setVar("body_class", 'defaultClass');
    }
}
