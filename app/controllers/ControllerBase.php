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
				'attendance' => array('savepost'),
				'holiday' => array('savepost'),
			);

			// Private developer resources
			$developerResources = array(
				'index' => array('index'),
				'project' => array('index', 'create', 'createpost', 'saveusers', 'savepost'),
				'task' => array('savepost', 'view', 'subscribe', 'unsubscribe', 'addcomment', 'index'),
				'user' => array('logout', 'myaccount'),
				'attendance' => array('savepost'),
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

	protected function getTodaysTotalTime($user)
	{
		$results = null;
		$results = $this->modelsManager->executeQuery('SELECT SEC_TO_TIME( SUM( TIME_TO_SEC( Attendance.total ) ) ) AS todays_time FROM Attendance WHERE user_id = "' . $user->id . '" AND date = CURDATE()');

		$todays_time = '00:00:00';

		foreach ($results AS $result) {
			$todays_time = $result->todays_time;
		}

		return $todays_time;
	}

	protected function getMonthsTotalTime($user)
	{
		$results = null;
		$results = $this->modelsManager->executeQuery('SELECT SEC_TO_TIME( SUM( TIME_TO_SEC( Attendance.total ) ) ) AS months_time FROM Attendance WHERE user_id = "' . $user->id . '" AND YEAR(Attendance.date) = YEAR(NOW()) AND MONTH(Attendance.date) = MONTH(NOW())');

		$months_time = '00:00:00';

		foreach ($results AS $result) {
			$months_time = $result->months_time;
		}

		return $months_time;
	}

	protected function getThisMonthsHolidays()
	{
		$results = null;
		$results = $this->modelsManager->executeQuery('SELECT Holiday.date FROM Holiday WHERE YEAR(Holiday.date) = YEAR(NOW()) AND MONTH(Holiday.date) = MONTH(NOW())');

		$holidays = array();

		foreach ($results AS $result) {
			$holidays[] = $result->date;
		}

		return $holidays;
	}

	protected function getMonthsTargetTime($user)
	{
		$startDate = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
		$endDate = date('Y-m-t', mktime(0, 0, 0, date('m'), 1, date('Y')));
		$holidays = $this->getThisMonthsHolidays();

		$workingDays = AttendanceHelper::getWorkingDays($startDate, $endDate, $holidays);

		return ($workingDays * 8) . ':00:00';
	}

	protected function initialize()
	{
		$this->getMonthsTargetTime(null);
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
			$this->view->setVar('todays_time', $this->getTodaysTotalTime($this->currentUser));
			$this->view->setVar('months_time', $this->getMonthsTotalTime($this->currentUser));
			$this->view->setVar('months_target_time', $this->getMonthsTargetTime($this->currentUser));

			$attendance = Attendance::findFirst('user_id="' . $this->currentUser->id . '" AND date=' . new Phalcon\Db\RawValue('CURDATE()') . ' AND end IS NULL');

			if ($attendance) {
				$this->view->setVar('attendance', $attendance);
			}
			else {
				$this->view->setVar('attendance', null);
			}
		}

		Phalcon\Tag::prependTitle('Project Manager | ');

		$controller = $this->dispatcher->getControllerName();
		$action = $this->dispatcher->getActionName();

		$acl = $this->_getAcl();

		$allowed = $acl->isAllowed($role, $controller, $action);

		if ($allowed != Phalcon\Acl::ALLOW) {
			if ($role == 'guest') {
				$this->flashSession->error('Please login before you proceed.');
				$this->dispatcher->forward(
					array(
						'controller' => 'user',
						'action' => 'login'
					)
				);
				return;
			}
			else {
				$this->flashSession->error('You do not have permission to access this area.');
				$this->dispatcher->forward(
					array(
						'controller' => 'project',
						'action' => 'index'
					)
				);
				return;
			}
		}
	}
}
