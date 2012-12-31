<?php

class AdminController extends ControllerBase
{
	public function indexAction()
	{
		Phalcon\Tag::appendTitle('Administration');
		$this->view->setVar('developers', User::getAllDevelopers());
		$this->view->setVar('holidays', Holiday::getFutureHolidays());
	}
}