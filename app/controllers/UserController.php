<?php

class UserController extends ControllerBase
{
    public function loginAction()
    {
        if ($this->request->isPost()) {
            $email = $this->request->getPost('email', 'string');
            $password = $this->request->getPost('password');

            $user = User::findFirst('email="' . $email . '" AND is_active="1"');

            if ($user) {
                $Bcrypt = new Bcrypt();

                if ($Bcrypt->verify($password, $user->password)) {
                    $this->session->set('user_id', $user->id);
                    $this->flashSession->success('Welcome ' . $user->full_name . '!');

                    $this->response->redirect('dashboard/index');
                    $this->view->disable();
                    return;
                }
            }

            $this->flashSession->error('Email and/or password incorrect.');
        }

        Phalcon\Tag::setTitle('Log in');
    }

    public function logoutAction()
    {
        $this->session->remove('user_id');
        $this->flashSession->success('You have been successfully logged out.');
        $this->response->redirect('user/login');
        $this->view->disable();
        return;
    }
}
