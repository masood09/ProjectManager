<?php

class AttendanceController extends ControllerBase
{
	public function savepostAction()
	{
		if ($this->request->isPost()) {
			$user_id = $this->currentUser->id;
			$attendance_id = $this->request->getPost('attendance_id');
			$date = new Phalcon\Db\RawValue('CURDATE()');
			$now = new Phalcon\Db\RawValue('now()');

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
}
