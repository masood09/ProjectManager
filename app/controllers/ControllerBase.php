<?php

class ControllerBase extends \Phalcon\Mvc\Controller
{
	protected $session_id = null;
	protected $currentUser = null;

	/**
	 * @var Phalcon\Acl\Adapter\Memory
	 */
	protected $_acl;

	protected function _getAcl()
	{
		if (!$this->_acl) {

			$acl = new Phalcon\Acl\Adapter\Memory();

			$acl->setDefaultAction(Phalcon\Acl::DENY);

			// Register roles
			$roles = array(
				'admin' => new Phalcon\Acl\Role('admin'),
				'developer' => new Phalcon\Acl\Role('developer'),
				'guest' => new Phalcon\Acl\Role('guest')
			);

			foreach ($roles as $role) {
				$acl->addRole($role);
			}

			// Private area resources
			$adminResources = array(
				'index' => array('index'),
				'project' => array('index', 'create', 'createpost', 'saveusers', 'savepost'),
				'task' => array('savepost', 'view', 'subscribe', 'unsubscribe', 'addcomment', 'index'),
				'admin' => array('index'),
				'user' => array('saveuser', 'myaccount'),
			);

			// Private developer resources
			$developerResources = array(
				'index' => array('index'),
				'project' => array('index', 'create', 'createpost', 'saveusers', 'savepost'),
				'task' => array('savepost', 'view', 'subscribe', 'unsubscribe', 'addcomment', 'index'),
				'user' => array('logout', 'myaccount'),
			);

			foreach ($adminResources as $resource => $actions){
				$acl->addResource(new Phalcon\Acl\Resource($resource), $actions);
			}

			foreach ($developerResources as $resource => $actions){
				$acl->addResource(new Phalcon\Acl\Resource($resource), $actions);
			}

			// Public area resources
			$publicResources = array(
				'user' => array('login', 'loginpost', 'test'),
			);

			foreach ($publicResources as $resource => $actions) {
				$acl->addResource(new Phalcon\Acl\Resource($resource), $actions);
			}

			// Grant access to public areas to both users and guests
			foreach ($roles as $role) {
				foreach($publicResources as $resource => $actions){
					$acl->allow($role->getName(), $resource, '*');
				}
			}

			// Grant acess to admin area to role Admin
			foreach ($adminResources as $resource => $actions) {
				foreach($actions as $action){
					$acl->allow('admin', $resource, $action);
				}
			}

			// Grant acess to developer area to role Developer
			foreach ($developerResources as $resource => $actions) {
				foreach($actions as $action){
					$acl->allow('developer', $resource, $action);
				}
			}

			$this->_acl = $acl;
		}

		return $this->_acl;
	}

	protected function initialize()
	{
		$role = null;
		$session_id = $this->session->get('session_id');
		$role = SessionHelper::getUserRole($session_id);

		if (is_null($role)) {
			$role = 'guest';
		}
		else {
			$role = $role->code;
		}

		if (!$session_id) {
			$this->session_id = null;
			$this->currentUser = null;
			$this->view->setVar('currentUser', $this->currentUser);
		}
		else {
			$this->session_id = $session_id;
			$this->currentUser = SessionHelper::getUser($session_id);
			$this->view->setVar('currentUser', $this->currentUser);
		}

		Phalcon\Tag::prependTitle('Project Manager | ');

		$controller = $this->dispatcher->getControllerName();
		$action = $this->dispatcher->getActionName();

		$acl = $this->_getAcl();

		$allowed = $acl->isAllowed($role, $controller, $action);

		if ($allowed != Phalcon\Acl::ALLOW) {
			if ($role == 'guest') {
				$this->flashSession->error('Please login before you proceed.');
				$this->response->redirect('user/login');
				$this->view->disable();
				return;
			}
			else {
				$this->flashSession->error('You do not have permission to access this area.');
				$this->response->redirect('project/index');
				$this->view->disable();
				return;
			}
		}
	}
}
