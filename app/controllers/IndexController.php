<?php

class IndexController extends ControllerBase
{
    public function indexAction()
    {
        $this->response->redirect('dashboard/index/');
        $this->view->disable();
        return;
    }
}
