<?php

class UserController extends ControllerBase
{
	public function testAction()
	{
		$task = Task::findFirst('id="5"');
		$this->Email->sendTaskClosedEmail($task);
		die;
		// $this->Email->sendWelcomeEmail($user, '123456');
		die;
	}

	public function loginAction($returnUrl=null)
	{
		$url = $_GET['_url'];
		$url_array = explode("/", $url);
		$url = null;

		if ($url_array[1] == 'user' && $url_array[2] == 'login') {
			if ($returnUrl == null) {
				$this->view->setVar('returnUrl', base64_encode('project/index'));
			}
			else {
				$this->view->setVar('returnUrl', $returnUrl);
			}
		}
		else {
			foreach($url_array AS $_url) {
				if ($_url != '') {
					$url .= $_url . '/';
				}
			}

			if ($url != null) {
				$this->view->setVar('returnUrl', base64_encode($url));
			}
			else {
				$this->view->setVar('returnUrl', base64_encode('project/index'));
			}
		}

		Phalcon\Tag::appendTitle('Log in to Project');
		$this->session->remove('session_id');
	}

	public function loginpostAction()
	{
		if ($this->request->isPost()) {
			$email = $this->request->getPost('email', 'string');
			$password = $this->request->getPost('password');
			$returnUrl = $this->request->getPost('returnUrl');

			$user = User::findFirst('email="' . $email . '" AND is_active="1"');

			if ($user != false) {
				$Bcrypt = new Bcrypt();

				if ($Bcrypt->verify($password, $user->password)) {
					$session_id = SessionHelper::registerSession($user);
					$this->session->set('session_id', $session_id);

					$this->flashSession->success('Welcome ' . $user->full_name . '!');
					$this->response->redirect(base64_decode($returnUrl));
					$this->view->disable();
					return;
				}
			}

			$this->flashSession->error('Email and/or password incorrect.');
		}

		$this->response->redirect('user/login/' . $returnUrl);
		$this->view->disable();
		return;
	}

	public function logoutAction()
	{
		$session_id = $this->session->get('session_id');
		SessionHelper::destroySession($session_id);

		$this->session->remove('session_id');
        $this->flashSession->success('You have been successfully logged out.');
        $this->response->redirect('user/login');
        $this->view->disable();
		return;
	}

	public function saveuserAction($id=null)
	{
		$sendWelcomeEmail = false;

		if ($this->request->isPost()) {
			$controller = $this->request->getPost('controller', 'string');
			$action = $this->request->getPost('action', 'string');

			$password1 = $this->request->getPost('password1');
			$password2 = $this->request->getPost('password2');

			if ($id == null && $this->currentUser->role_id == 1) {
				$user = new User();

				if ($password1 !== $password2) {
					$this->flashSession->error('Passwords do not match.');
					$this->response->redirect($controller . '/' . $action);
					$this->view->disable();
					return;
				}

				$Bcrypt = new Bcrypt();
				$user->password = $Bcrypt->hash($password1);
				$user->created_at = new Phalcon\Db\RawValue('now()');

				$role_id = $this->request->getPost('role_id');

				$user->role_id = $role_id;

				$sendWelcomeEmail = true;
			}
			else {
				if ($this->currentUser->role_id == 1) {
					$user = User::findFirst('id="' . $id . '"');
					$role_id = $this->request->getPost('role_id');

					if ($role_id) {
						$user->role_id = $role_id;
					}
				}
				else {
					$user = $this->currentUser;
				}

				if (!$user) {
					$this->flashSession->error('Requested user not found.');
					$this->response->redirect($controller . '/' . $action);
					$this->view->disable();
					return;
				}

				if ($password1 != null) {
					if ($password1 !== $password2) {
						$this->flashSession->error('Passwords do not match.');
						$this->response->redirect($controller . '/' . $action);
						$this->view->disable();
						return;
					}

					$Bcrypt = new Bcrypt();

					if ($this->currentUser->role_id != 1) {
						$old_password = $this->request->getPost('old_password');

						if (!$Bcrypt->verify($old_password, $user->password)) {
							$this->flashSession->error('You did not enter correct password.');
							$this->response->redirect($controller . '/' . $action);
							$this->view->disable();
							return;
						}
					}

					$user->password = $Bcrypt->hash($password1);
				}
			}

			$full_name = $this->request->getPost('full_name', 'string');
			$email = $this->request->getPost('email', 'string');

			$user->full_name = $full_name;
			$user->email = $email;
			$user->is_active = 1;

			if ($user->save() == true) {
				$this->flashSession->success('User account saved successfully.');

				if ($sendWelcomeEmail) {
					$this->Email->sendWelcomeEmail($user, $password1);
				}

				$this->response->redirect($controller . '/' . $action);
				$this->view->disable();
				return;
			}
			else {
    	    	foreach ($user->getMessages() as $message) {
    	    		$this->flashSession->error((string) $message);
            	}
    	    }

    	    $this->response->redirect($controller . '/' . $action);
    	    $this->view->disable();
			return;
		}

		$this->response->redirect('project/index');
		$this->view->disable();
		return;
	}

	public function myaccountAction()
	{

	}
}
