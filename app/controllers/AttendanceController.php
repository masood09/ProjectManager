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

class AttendanceController extends ControllerBase
{
    public function startstopAction()
    {
        if ($this->request->isPost()) {
            $task_id = (int)$this->request->getPost('task_id');
            $controller = $this->request->getPost('controller');
            $action = $this->request->getPost('action');

            if (!$controller || !$action) {
                $controller = 'dashboard';
                $action = 'index';
            }

            if (!$task_id && $task_id != 0) {
                $this->flashSession->error('No task selected!');
                $this->response->redirect($controller . '/' . $action);
                $this->view->disable();
                return;
            }

            if ($task_id != 0) {
                $task = Task::findFirst('id = "' . $task_id . '"');

                if (!$task) {
                    $this->flashSession->error('The specified task does not exist!');
                    $this->response->redirect($controller . '/' . $action);
                    $this->view->disable();
                    return;
                }

                if (!$task->getProject()->isInProject($this->currentUser)) {
                    $this->flashSession->error('The specified task does not exist!');
                    $this->response->redirect($controller . '/' . $action);
                    $this->view->disable();
                    return;
                }
            }

            $attendance = Attendance::findFirst('user_id = "' . $this->currentUser->id . '" AND task_id = "' . $task_id . '" AND date = CURDATE() AND end IS NULL');

            if ($attendance) {
                // Timer already running let's end the timer.
                $attendance->end = new Phalcon\Db\RawValue('now()');
                $attendance->updated_at = new Phalcon\Db\RawValue('now()');
                $attendance->save();

                if ($task) {
                    $task->hours_spent = $task->calculateTotalTimeSpent();
                    $task->save();
                }
            }
            else {
                // Timer not running. Lets start the timer.
                $attendance = new Attendance();
                $attendance->user_id = $this->currentUser->id;
                $attendance->task_id = $task_id;
                $attendance->date = new Phalcon\Db\RawValue('CURDATE()');
                $attendance->start = new Phalcon\Db\RawValue('now()');
                $attendance->created_at = new Phalcon\Db\RawValue('now()');
                $attendance->updated_at = new Phalcon\Db\RawValue('now()');
                $attendance->save();
            }

            $this->response->redirect($controller . '/' . $action);
            $this->view->disable();
            return;
        }

        $this->response->redirect('dashboard/index');
        $this->view->disable();
        return;
    }
}
