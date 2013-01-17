<?php
// Copyright (C) 2013 Masood Ahmed

// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.

// You should have received a copy of the GNU General Public License
// along with this program. If not, see <http://www.gnu.org/licenses/>.

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
