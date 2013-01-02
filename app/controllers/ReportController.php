<?php

class ReportController extends ControllerBase
{
	protected function _getRemainingHours($task_id, $date, $assigned_hours)
	{
		if ($assigned_hours == '00:00:00') {
			return '00:00:00';
		}

		$time_1 = $assigned_hours;
		$date_1 = explode(':', $time_1);
		$timestamp_1 = ($date_1[0]*60*60)+($date_1[1]*60)+$date_1[2];

		$results = $this->modelsManager->executeQuery('SELECT SEC_TO_TIME( SUM( TIME_TO_SEC( Attendance.total ) ) ) AS task_time FROM Attendance WHERE task_id = "' . $task_id . '" AND date <= "' . $date . '"');
		$time_2 = '00:00:00';

		foreach ($results AS $result) {
			if (!is_null($result->task_time)) {
				$time_2 = $result->task_time;
			}
		}

		$date_2 = explode(':', $time_2);
		$timestamp_2 = ($date_2[0]*60*60)+($date_2[1]*60)+$date_2[2];

		if ($timestamp_1 > $timestamp_2) {
			$diff = $timestamp_1 - $timestamp_2;
			$minus = false;
		}
		else {
			$diff = $timestamp_2 - $timestamp_1;
			$minus = true;
		}

		$days = (int)gmdate("j", $diff) - 1;
		$time_3 = gmdate("H:i:s", $diff);
		$date_3 = explode(':', $time_3);

		$hours = ($days * 24) + (int)$date_3[0];
		$minutes = (int)$date_3[1];
		$seconds = (int)$date_3[2];

		$return = $hours . ':' . $minutes . ':' . $seconds;

		if ($minus) {
			$return = '-' . $return;
		}

		return $return;
	}

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
							$temp['time_remaining'] = $this->_getRemainingHours($task_id, $date, $task->hours);

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
