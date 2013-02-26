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

class ReportController extends ControllerBase
{
    public function attendanceAction()
    {
        if ($this->currentUser->isAdmin()) {
            $allUsers = User::getAllUsers();
        }
        else {
            $allUsers = null;
        }

        if ($this->request->isPost()) {
            $month = $this->request->getPost('month');
            $year = $this->request->getPost('year');

            if ($this->currentUser->isAdmin()) {
                $user_id = $this->request->getPost('user_id');
                $user = User::findFirst('id = "' . $user_id . '"');

                if (!$user) {
                    $user = $this->currentUser;
                }
            }
            else {
                $user = $this->currentUser;
            }
        }
        else {
            $month = date('m');
            $year = date('Y');
            $user = $this->currentUser;
            $user_id = $this->currentUser->id;
        }

        $startDate = date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
        $endDate = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));
        $monthsProductivity = 0;
        $monthsProductivityDays = 0;

        $holidays = AttendanceHelper::getHolidays($startDate, $endDate);
        $leaves = Leaves::find('user_id = "' . $user->id . '" AND date >= "' . $startDate . '" AND date <= "' . $endDate . '" AND approved = "1"');

        $leavesArray = array();

        foreach ($leaves AS $leave) {
            if (!in_array($leave->date, $holidays)) {
                $leavesArray[] = $leave->date;
            }
        }

        $i = 1;
        $no_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $records = array();
        $records['items'] = array();

        while ($i <= $no_days) {
            $temp = array();
            $date = date('Y-m-d', mktime(0, 0, 0, $month, $i, $year));

            $temp['Date'] = $date;
            $temp['Day'] = date('l', mktime(0, 0, 0, $month, $i, $year));
            (in_array($date, $holidays)) ? $temp['Holiday'] = true : $temp['Holiday'] = false;
            (in_array($date, $leavesArray)) ? $temp['Leaves'] = true : $temp['Leaves'] = false;
            (in_array(date('N', strtotime($date)), $user->getWeekOffs())) ? $temp['Weekoffs'] = true : $temp['Weekoffs'] = false;
            ($temp['Holiday'] == true || $temp['Leaves'] == true || $temp['Weekoffs'] == true) ? $temp['TargetTime'] = '00:00' : $temp['TargetTime'] = Config::getValue('attendance/days_target_time') . ':00';
            $temp['LoggedTime'] = $user->getDaysTime($i, $month, $year);
            $temp['Productivity'] = $user->getDaysProductivity($temp['LoggedTime'], $i, $month, $year) . '%';

            if ((int)str_replace('%', '', $temp['Productivity']) > 0) {
                $monthsProductivity += (int)str_replace('%', '', $temp['Productivity']);
                $monthsProductivityDays++;
            }

            $records['items'][] = $temp;
            $i++;
        }

        $daysTargetTime = (int)Config::getValue('attendance/days_target_time');
        $workingDays = AttendanceHelper::getWorkingDays($user->id, $startDate, $endDate);
        $_targetTime = ($daysTargetTime * $workingDays) . ':00';
        $records['MonthsTargetTime'] = $_targetTime;
        $records['MonthsTime'] = $user->getMonthsTime($month, $year);
        $records['MonthsProductivity'] = ceil($monthsProductivity / $monthsProductivityDays) . '%';

        $this->view->setVar('records', $records);
        $this->view->setVar('allUsers', $allUsers);
        $this->view->setVar('body_id', 'report_attendance');
        $this->view->setVar('attendanceMonth', $month);
        $this->view->setVar('attendanceYear', $year);
        $this->view->setVar('attendanceUserId', $user_id);
        Phalcon\Tag::setTitle('Reports | Attendance');
    }

    public function workreportAction()
    {
        $this->view->setVar('body_id', 'report_workreport');
        Phalcon\Tag::setTitle('Reports | Work Report');
    }
}
