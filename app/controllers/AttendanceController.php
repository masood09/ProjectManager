<?php

class AttendanceController extends ControllerBase
{
	public function savepostAction()
	{
		if ($this->request->isPost()) {
			$user_id = $this->currentUser->id;
			$attendance_id = $this->request->getPost('attendance_id');
			$task_id = $this->request->getPost('task_id');
			$date = new Phalcon\Db\RawValue('CURDATE()');
			$now = new Phalcon\Db\RawValue('now()');

			if (is_null($task_id)) {
				$task_id = 0;
			}

			$controller = $this->request->getPost('controller');
			$action = $this->request->getPost('action');

			if (!is_null($attendance_id)) {
				// Ending the timer
				$attendance = Attendance::findFirst('id="' . $attendance_id . '"');

				if (!$attendance) {
					$this->response->redirect('project/index');
					$this->view->disable();
					return;
				}

				$attendance->end = $now;
				$attendance->total = new Phalcon\Db\RawValue('TIMEDIFF(' . $now . ', "' . $attendance->start . '")');
			}
			else {
				$attendance = new Attendance();
				$attendance->user_id = $user_id;
				$attendance->date = $date;
				$attendance->task_id = $task_id;
				$attendance->start = $now;
			}

			if ($attendance->save() != true) {
				foreach ($attendance->getMessages() as $message) {
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

	public function indexAction()
	{
		$records = array();

		$month = date('m');
		$year = date('Y');
		$user_id = $this->currentUser->id;

		$startDate = date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
		$endDate = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));
		$holidays = $this->getMonthsHolidays($month, $year);

		$i = 1;
		$no_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);

		while ($i <= $no_days) {
			$temp = array();
			$date = date('Y-m-d', mktime(0, 0, 0, $month, $i, $year));

			$temp['date'] = $date;
			$temp['day'] = date('l', mktime(0, 0, 0, $month, $i, $year));
			(in_array($date, $holidays)) ? $temp['holiday'] = true : $temp['holiday'] = false;
			(in_array($date, $holidays) || in_array($temp['day'], array('Sunday', 'Saturday'))) ? $temp['target_time'] = '00:00:00' : $temp['target_time'] = '08:00:00';
			$temp['logged_time'] = $this->getDaysTotalTime($user_id, $date);

			$records[] = $temp;
			$i++;
		}

		$this->view->setVar('records', $records);
		$this->view->setVar('report_months_target_time', $this->getMonthsTargetTime($user_id, $month, $year));
		$this->view->setVar('report_months_total_time', $this->getMonthsTotalTime($user_id, $month, $year));

		Phalcon\Tag::setTitle('Attendance');
	}
}
