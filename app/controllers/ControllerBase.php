<?php

class ControllerBase extends Phalcon\Mvc\Controller
{
    protected function initialize()
    {
    	$this->view->setVar("body_id", 'defaultId');
    	$this->view->setVar("body_class", 'defaultClass');
    }
}
