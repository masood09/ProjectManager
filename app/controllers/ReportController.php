<?php

class ReportController extends ControllerBase
{
	protected function _getWorkReport($user_id, $from, $to)
	{
		$reports = array();

		$dates = array();
		$results = $this->modelsManager->executeQuery('SELECT Attendance.date, Attendance.task_id FROM Attendance WHERE user_id = "' . $user_id . '" AND date >= "' . $from . '" AND date <= "' . $to . '"');

		foreach ($results AS $result) {
			if (!is_null($result->date) && !is_null($result->task_id)) {
				if (!isset($dates[$result->date])) {
					$dates[$result->date] = array();
				}

				$dates[$result->date][$result->task_id] = $result->task_id;
			}
		}

		foreach($dates AS $date => $task_ids) {
			foreach($task_ids AS $task_id) {
				$results = $this->modelsManager->executeQuery('SELECT SEC_TO_TIME( SUM( TIME_TO_SEC( Attendance.total ) ) ) AS task_time FROM Attendance WHERE user_id = "' . $user_id . '" AND date = "' . $date . '" AND task_id = "' . $task_id . '"');

				foreach ($results AS $result) {
					if (!is_null($result->task_time)) {
						$task = Task::findFirst('id = ' . $task_id);

						if ($task) {
							$temp = array();
							$temp['date'] = $date;
							$temp['job_id'] = $task->job_id;
							$temp['project'] = $task->getProject()->name;
							$temp['task_title'] = $task->title;
							$temp['time_spent'] = $result->task_time;
							$temp['assigned_time'] = $task->hours;

							if ($date == $task->completed_on) {
								$temp['completed'] = 'Yes';
							}
							else {
								$temp['completed'] = 'No';
							}

							$reports[] = $temp;
						}
					}
				}
			}
		}

		return $reports;
	}

	public function indexAction()
	{
		$user_id = $this->request->getPost('user_id');

		if (is_null($user_id)) {
			$user_id = $this->currentUser->id;
		}

		$from = $this->request->getPost('from');
		$to = $this->request->getPost('to');

		$reports = null;

		if (is_null($from) || is_null($to) || $from == '' || $to == '') {
			$this->view->setVar('reports', $reports);
			$this->view->setVar('post_from', $from);
			$this->view->setVar('post_to', $to);
			$this->view->setVar('post_user_id', $user_id);
		}
		else {
			$reports = $this->_getWorkReport($user_id, $from, $to);

			$this->view->setVar('reports', $reports);
			$this->view->setVar('post_from', $from);
			$this->view->setVar('post_to', $to);
			$this->view->setVar('post_user_id', $user_id);
		}

		Phalcon\Tag::setTitle('Work Report');
	}
}
