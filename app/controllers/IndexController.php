<?php

class IndexController extends ControllerBase
{
	public function indexAction()
	{
		echo $this->session_id; die;
	}
}
