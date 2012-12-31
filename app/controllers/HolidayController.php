<?php

class HolidayController extends ControllerBase
{
	public function savepostAction()
	{
		if ($this->request->isPost()) {
			$holiday_id = $this->request->getPost('holiday_id');
			$name = $this->request->getPost('name');
			$date = $this->request->getPost('date');

			$controller = $this->request->getPost('controller');
			$action = $this->request->getPost('action');

			if (!is_null($holiday_id)) {
				// Ending the holiday
				$holiday = Holiday::findFirst('id="' . $holiday_id . '"');

				if (!$holiday) {
					$this->response->redirect('project/index');
					$this->view->disable();
					return;
				}
			}
			else {
				$holiday = new Holiday();
			}

			$holiday->name = $name;
			$holiday->date = $date;

			if ($holiday->save() != true) {
				foreach ($holiday->getMessages() as $message) {
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
