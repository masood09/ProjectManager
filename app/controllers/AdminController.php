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

class AdminController extends ControllerBase
{
    public function indexAction()
    {
        $this->view->setVar('core_name', Config::getValue('core/name'));
        $this->view->setVar('core_email', Config::getValue('core/email'));

        $this->view->setVar('email_host', Config::getValue('email/host'));
        $this->view->setVar('email_username', Config::getValue('email/username'));
        $this->view->setVar('email_password', Config::getValue('email/password'));
        $this->view->setVar('email_port', Config::getValue('email/port'));
        $this->view->setVar('email_ssl', Config::getValue('email/ssl'));

        $this->view->setVar('attendance_days_target_time', Config::getValue('attendance/days_target_time'));
        $this->view->setVar('attendance_leaves_per_month', Config::getValue('attendance/leaves_per_month'));
        $this->view->setVar('attendance_leaves_per_quarter', Config::getValue('attendance/leaves_per_quarter'));
        $this->view->setVar('attendance_leaves_per_year', Config::getValue('attendance/leaves_per_year'));
        $this->view->setVar('attendance_leaves_method', Config::getValue('attendance/leaves_method'));
        $this->view->setVar('attendance_leaves_carries', Config::getValue('attendance/leaves_carries'));
        $this->view->setVar('attendance_weekoffs', explode(',', Config::getValue('attendance/weekoffs')));

        $this->view->setVar('body_id', 'admin_manage');
        Phalcon\Tag::setTitle('Administration - Manage');
    }

    public function applyleaveAction()
    {
        if ($this->request->isPost()) {
            $from = $this->request->getPost('leavesFrom');
            $to = $this->request->getPost('leavesTo');
            $reason = $this->request->getPost('leavesReason');
            $user = User::findFirst('id = "' . $this->request->getPost('leavesUser') . '"');
            $approved = $this->request->getPost('leavesApproved');

            if (!$user) {
                $this->view->disable();
                return;
            }

            if (!$reason) {
                $reason = '';
            }

            $appliedLeaves = array();
            $Bcrypt = new Bcrypt();
            $uuid = $Bcrypt->hash($user->id . $user->email . time());
            $holidays = AttendanceHelper::getHolidays($from, $to);

            $counterFrom = strtotime($from);
            $counterTo = strtotime($to);

            while(strtotime('+1 day', $counterFrom) <= strtotime('+1 day', $counterTo)) {
                echo date('Y-m-d', $counterFrom) . "<br>";
                if (
                    !in_array(date('N', $counterFrom), $user->getWeekOffs())
                    && !in_array(date('Y-m-d', $counterFrom), $holidays)
                ) {
                    $appliedLeaves[] = date('Y-m-d', $counterFrom);
                }

                $counterFrom = strtotime('+1 day', $counterFrom);
            }

            foreach ($appliedLeaves AS $appliedLeave) {
                $leave = Leaves::findFirst('user_id = "' . $user->id . '" AND date = ' . $appliedLeave);

                if (!$leave) {
                    $leave = new Leaves();
                    $leave->user_id = $user->id;
                    $leave->date = $appliedLeave;
                    $leave->reason = $reason;
                    $leave->approved = $approved;
                    $leave->approved_by = $this->currentUser->id;
                    $leave->created_at = new Phalcon\Db\RawValue('now()');
                    $leave->save();
                }
            }

            $this->view->disable();
            return;
        }

        $this->response->redirect('dashboard/index');
        $this->view->disable();
        return;
    }

    public function leavesAction()
    {
        $allUsers = User::find(array(
            'conditions' => 'is_active = "1"',
            'order' => 'full_name ASC',
        ));

        $this->view->setVar('allUsers', $allUsers);
        $this->view->setVar('body_id', 'admin_leaves');
        Phalcon\Tag::setTitle('Manage Leaves');
    }

    public function getallleavesajaxAction()
    {
        $start = $this->request->getQuery('start');
        $end = $this->request->getQuery('end');
        $leavesArray = array();

        $leaves = Leaves::find(array(
            'conditions' => 'date >= "' . date('Y-m-d', $start) . '" AND date <= "' . date('Y-m-d', $end) . '"',
            'order' => 'date DESC',
        ));

        foreach ($leaves AS $leave) {
            $temp = array();
            $temp['id'] = 'leave_' . $leave->id;
            $temp['title'] = $leave->getUser()->full_name;
            $temp['start'] = $leave->date;
            $temp['allDay'] = true;

            if (is_null($leave->approved)) {
                $temp['color'] = '#fcf8e3';
            }
            else if ($leave->approved == 1) {
                $temp['color'] = '#dff0d8';
            }
            else if ($leave->approved == 0) {
                $temp['color'] = '#f2dede';
            }

            $temp['textColor'] = '#333333';

            $temp['eventType'] = 'leave';

            $temp['leaveId'] = $leave->id;
            $temp['leaveDate'] = $leave->date;
            $temp['leaveReason'] = $leave->reason;
            $temp['leaveApproved'] = $leave->approved;

            $leavesArray[] = $temp;
        }

        $holidays = Holiday::find(array(
            'conditions' => 'date >= "' . date('Y-m-d', $start) . '" AND date <= "' . date('Y-m-d', $end) . '"',
            'order' => 'date DESC',
        ));

        foreach ($holidays AS $holiday) {
            $temp = array();
            $temp['id'] = 'holiday_' . $holiday->id;
            $temp['title'] = $holiday->name;
            $temp['start'] = $holiday->date;
            $temp['allDay'] = true;
            $temp['color'] = '#d9edf7';
            $temp['textColor'] = '#333333';

            $temp['eventType'] = 'holiday';

            $temp['holidayId'] = $holiday->id;
            $temp['holidayName'] = $holiday->name;
            $temp['holidayDate'] = $holiday->date;

            $leavesArray[] = $temp;
        }

        echo json_encode($leavesArray);

        $this->view->disable();
        return;
    }

    public function approveleaveAction()
    {
        if ($this->request->isPost()) {
            $leave_id = $this->request->getPost('leave_id');
            $approved = $this->request->getPost('approved');

            if (!in_array($approved, array(0, 1))) {
                $this->view->disable();
                return;
            }

            $leave = Leaves::findFirst('id = "' . $leave_id . '"');

            if (!$leave) {
                $this->view->disable();
                return;
            }

            $leave->approved = $approved;
            $leave->approved_by = $this->currentUser->id;
            $leave->save();

            $this->view->disable();
            return;
        }

        $this->response->redirect('dashboard/index');
        $this->view->disable();
        return;
    }
}
