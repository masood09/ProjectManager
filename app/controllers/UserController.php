<?php

class UserController extends ControllerBase
{
    public function loginAction()
    {

    }

    public function logoutAction()
    {
        $this->session->remove('user_id');
        $this->flashSession->success('You have been successfully logged out.');
        $this->response->redirect('user/login/');
        $this->view->disable();
        return;
    }
}
