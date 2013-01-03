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
				'project' => array('index', 'createpost', 'saveusers', 'savepost', 'view'),
				'task' => array('savepost', 'view', 'subscribe', 'unsubscribe', 'addcomment', 'index'),
				'admin' => array('index', 'configpost'),
				'user' => array('saveuser', 'myaccount'),
				'attendance' => array('savepost', 'index'),
				'holiday' => array('savepost'),
				'report' => array('index'),
			);

			// Private developer resources
			$developerResources = array(
				'index' => array('index'),
				'project' => array('index', 'view'),
				'task' => array('savepost', 'view', 'subscribe', 'unsubscribe', 'addcomment', 'index'),
				'user' => array('logout', 'myaccount'),
				'attendance' => array('savepost', 'index'),
				'report' => array('index'),
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

	protected function getDaysTotalTime($user_id, $date=null)
	{
		if ($date == null) {
			$date = 'CURDATE()';
		}
		else {
			$date = '"' . $date . '"';
		}

		$results = null;
		$results = $this->modelsManager->executeQuery('SELECT SEC_TO_TIME( SUM( TIME_TO_SEC( Attendance.total ) ) ) AS todays_time FROM Attendance WHERE user_id = "' . $user_id . '" AND date = ' . $date);

		$days_time = '00:00:00';

		foreach ($results AS $result) {
			if (!is_null($result->todays_time)) {
				$days_time = $result->todays_time;
				break;
			}
		}

		return $days_time;
	}

	protected function getMonthsTotalTime($user_id, $month=null, $year=null)
	{
		if ($month == null) {
			$month = 'MONTH(NOW())';
		}

		if ($year == null) {
			$year = 'YEAR(NOW())';
		}

		$results = null;
		$results = $this->modelsManager->executeQuery('SELECT SEC_TO_TIME( SUM( TIME_TO_SEC( Attendance.total ) ) ) AS months_time FROM Attendance WHERE user_id = "' . $user_id . '" AND YEAR(Attendance.date) = ' . $year . ' AND MONTH(Attendance.date) = ' . $month);

		$months_time = '00:00:00';

		foreach ($results AS $result) {
			if (!is_null($result->months_time)) {
				$months_time = $result->months_time;
				break;
			}
		}

		return $months_time;
	}

	protected function getMonthsHolidays($month=null, $year=null)
	{
		if ($month == null) {
			$month = 'MONTH(NOW())';
		}

		if ($year == null) {
			$year = 'YEAR(NOW())';
		}

		$results = null;
		$results = $this->modelsManager->executeQuery('SELECT Holiday.date FROM Holiday WHERE YEAR(Holiday.date) = ' . $year . ' AND MONTH(Holiday.date) = ' . $month);

		$holidays = array();

		foreach ($results AS $result) {
			$holidays[] = $result->date;
		}

		return $holidays;
	}

	protected function getMonthsTargetTime($user_id, $month=null, $year=null)
	{
		if ($month == null) {
			$month = date('m');
		}

		if ($year == null) {
			$year = date('Y');
		}

		$startDate = date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
		$endDate = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));
		$holidays = $this->getMonthsHolidays($month, $year);

		$workingDays = AttendanceHelper::getWorkingDays($startDate, $endDate, $holidays);

		return ($workingDays * 8) . ':00:00';
	}

	protected function getUserTasksForSelect($user)
	{
		$return = null;

		$allTasks = $user->getAllTasks();

		foreach ($allTasks AS $task) {
			if ($task->status == 0) {
				$return[$task->id] = $task->getProject()->name . ' - ' . $task->title;
			}
		}

		return $return;
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
			$_currentUser = SessionHelper::getUser($session_id);

			if (!is_null($_currentUser)) {
				$this->session_id = $session_id;
				$this->currentUser = $_currentUser;
				$this->view->setVar('currentUser', $this->currentUser);
				$this->view->setVar('todays_time', $this->getDaysTotalTime($this->currentUser->id));
				$this->view->setVar('months_time', $this->getMonthsTotalTime($this->currentUser->id));
				$this->view->setVar('months_target_time', $this->getMonthsTargetTime($this->currentUser->id));
				$this->view->setVar('tasks_select', $this->getUserTasksForSelect($this->currentUser));
				$this->view->setVar('extra_params', '');

				if ($this->currentUser->getRole()->code == 'admin') {
					$this->view->setVar('allUsers', User::getAllDevelopers(true));
				}
				else {
					$this->view->setVar('allUsers', null);
				}

				$attendance = Attendance::findFirst('user_id="' . $this->currentUser->id . '" AND date=' . new Phalcon\Db\RawValue('CURDATE()') . ' AND end IS NULL');

				if ($attendance) {
					$this->view->setVar('attendance', $attendance);
				}
				else {
					$this->view->setVar('attendance', null);
				}
			}
			else {
				$this->session_id = null;
				$this->currentUser = null;
				$this->view->setVar('currentUser', $this->currentUser);
			}
		}

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
