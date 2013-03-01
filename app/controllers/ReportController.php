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
    protected function _generateReport($user, $startDate, $endDate)
    {
        $reports = null;

        $dbReports = ReportDaily::find(array(
            'conditions' => 'user_id = "' . $user->id . '" AND date >= "' . $startDate . '" AND date <= "' . $endDate . '"',
            'order' => 'date ASC',
        ));

        $results = $this->modelsManager->executeQuery('SELECT MAX( logged_hours ) AS max_logged_hours, MAX ( no_real_tasks_worked )  AS max_no_real_tasks_worked FROM ReportDaily WHERE user_id = "' . $user->id . '" AND date >= "' . $startDate . '" AND date <= "' . $endDate . '"');
        $holidays = AttendanceHelper::getHolidays($startDate, $endDate);
        $leaves = $user->getApprovedLeaveDates();

        foreach ($results AS $result) {
            if (is_null($result->max_logged_hours)) {
                return $reports;
            }

            $_explode = explode(':', $result->max_logged_hours);
            $max_logged_hours = ($_explode[0] * 3600) + ($_explode[1] * 60) + $_explode[2];
            $max_no_real_tasks_worked = $result->max_no_real_tasks_worked;
        }

        foreach ($dbReports AS $dbReport) {
            $temp = array();
            $barLabel = '';

            $_explode = explode(':', $dbReport->logged_hours);
            $temp['title'] = date('j M', strtotime($dbReport->date));
            $temp['logged_hours_value'] = round(((($_explode[0] * 3600) + ($_explode[1] * 60) + $_explode[2]) / $max_logged_hours), 4) * 100;
            $temp['logged_hours_label'] = $_explode[0] .  ':' . $_explode[1];
            $temp['productivity'] = $dbReport->productivity;
            $temp['no_real_tasks_worked_value'] = round(($dbReport->no_real_tasks_worked / $max_no_real_tasks_worked), 4) * 100;
            $temp['no_real_tasks_worked_label'] = $dbReport->no_real_tasks_worked;

            if (in_array($dbReport->date, $holidays)) {
                $holiday = Holiday::findFirst('date = "' . $dbReport->date . '"');
                $barLabel = $holiday->name;
            }
            else if (in_array(date('N', strtotime($dbReport->date)), $user->getWeekOffs())) {
                $barLabel = date('l', strtotime($dbReport->date));
            }
            else if (in_array($dbReport->date, $leaves)) {
                $barLabel = 'Approved Leave';
            }
            else if ($temp['logged_hours_value'] == 0) {
                $barLabel = 'Unscheduled Leave';
            }

            $temp['barLabel'] = $barLabel;
            $temp['isWeekOff'] = (in_array(date('N', strtotime($dbReport->date)), $user->getWeekOffs())) ? true : false;
            $temp['isHoliday'] = (in_array($dbReport->date, $holidays)) ? true : false;
            $temp['isLeave'] = (in_array($dbReport->date, $leaves)) ? true : false;

            $reports[] = $temp;
        }

        return $reports;
    }

    public function indexAction($userId = null)
    {
        if (is_null($userId)) {
            $userId = $this->currentUser->id;
        }
        else if (!$this->currentUser->isAdmin()) {
            $userId = $this->currentUser->id;
        }

        $user = User::findFirst('id = "' . $userId . '"');

        if (!$user) {
            $user = $this->currentUser;
        }

        if ($this->currentUser->isAdmin()) {
            $allUsers = User::getAllUsers();
        }
        else {
            $allUsers = null;
        }

        $startDate = date('Y-m-d', strtotime("-15 days", time()));
        $endDate = date('Y-m-d', strtotime("-1 days", time()));

        $reports = $this->_generateReport($user, $startDate, $endDate);

        $attendanceStat = $user->getAttendanceStats();

        $this->view->setVar('reportSummaryTitle', 'All time summary');
        $this->view->setVar('attendanceStat', $attendanceStat);
        $this->view->setVar('reportSummaryUserId', $userId);
        $this->view->setVar('reportSummaryStartDate', $startDate);
        $this->view->setVar('reportSummaryEndDate', $endDate);
        $this->view->setVar('reportSummaryUser', $user);
        $this->view->setVar('reports', $reports);
        $this->view->setVar('allUsers', $allUsers);
        $this->view->setVar('url_params', $user->id);
        $this->view->setVar('body_id', 'report_summary');
        Phalcon\Tag::setTitle('Reports | Summary');
    }

    public function getsummaryajaxAction()
    {
        if ($this->request->isPost()) {
            if ($this->currentUser->isAdmin()) {
                $user_id = $this->request->getPost('userId');
                $user = User::findFirst('id = "' . $user_id . '"');

                if (!$user) {
                    $user = $this->currentUser;
                }
            }
            else {
                $user = $this->currentUser;
            }

            $dateRange = (string)$this->request->getPost('dateRange');

            if ($dateRange == '0') {
                $attendanceStat = $user->getAttendanceStats();
                $reportSummaryTitle = 'All time summary';
            }
            else {
                $_explode = explode('::', $dateRange);

                $attendanceStat = $user->getAttendanceStats($_explode[0], $_explode[1]);
                $reportSummaryTitle = 'Summary for the month ' . date('M, Y', strtotime($_explode[0]));
            }

            $this->view->setVar('reportSummaryTitle', $reportSummaryTitle);
            $this->view->setVar('reportSummaryUser', $user);
            $this->view->setVar('attendanceStat', $attendanceStat);
            $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
            $this->view->render('partials', 'report_summary_text');
            $this->view->finish();
            $data['summaryContent'] = $this->view->getContent();

            echo json_encode($data);
            $this->view->disable();
            return;
        }

        $this->response->redirect('dashboard/index');
        $this->view->disable();
        return;
    }

    public function getajaxreportprevAction()
    {
        if ($this->request->isPost()) {
            $curDate = $this->request->getPost('startDate');
            $userId = $this->request->getPost('userId');

            $startDate = date('Y-m-d', strtotime("-15 days", strtotime($curDate)));
            $endDate = date('Y-m-d', strtotime("-1 days", strtotime($curDate)));

            if (is_null($userId)) {
                $userId = $this->currentUser->id;
            }
            else if (!$this->currentUser->isAdmin()) {
                $userId = $this->currentUser->id;
            }

            $user = User::findFirst('id = "' . $userId . '"');

            if (!$user) {
                $user = $this->currentUser;
            }

            $reports = $this->_generateReport($user, $startDate, $endDate);

            $this->view->setVar('reportSummaryUser', $user);
            $this->view->setVar('reports', $reports);
            $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
            $this->view->render('partials', 'report_summary_charts');
            $this->view->finish();
            $data['chartContent'] = $this->view->getContent();
            $data['startDate'] = $startDate;
            $data['endDate'] = $endDate;

            echo json_encode($data);
            $this->view->disable();
            return;
        }

        $this->response->redirect('dashboard/index');
        $this->view->disable();
        return;
    }

    public function getajaxreportnextAction()
    {
        if ($this->request->isPost()) {
            $curDate = $this->request->getPost('startDate');
            $userId = $this->request->getPost('userId');

            $startDate = date('Y-m-d', strtotime("+15 days", strtotime($curDate)));
            $endDate = date('Y-m-d', strtotime("+30 days", strtotime($curDate)));

            if (is_null($userId)) {
                $userId = $this->currentUser->id;
            }
            else if (!$this->currentUser->isAdmin()) {
                $userId = $this->currentUser->id;
            }

            $user = User::findFirst('id = "' . $userId . '"');

            if (!$user) {
                $user = $this->currentUser;
            }

            $reports = $this->_generateReport($user, $startDate, $endDate);

            $this->view->setVar('reportSummaryUser', $user);
            $this->view->setVar('reports', $reports);
            $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
            $this->view->render('partials', 'report_summary_charts');
            $this->view->finish();
            $data['chartContent'] = $this->view->getContent();
            $data['startDate'] = $startDate;
            $data['endDate'] = $endDate;

            echo json_encode($data);
            $this->view->disable();
            return;
        }

        $this->response->redirect('dashboard/index');
        $this->view->disable();
        return;
    }

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
        $dates = array();
        $records = array();

        if ($this->currentUser->isAdmin()) {
            $allUsers = User::getAllUsers();
        }
        else {
            $allUsers = null;
        }

        if ($this->request->isPost()) {
            $user_id = $this->request->getPost('user_id');
            $workreportStartDate = $this->request->getPost('workreportFrom');
            $workreportEndDate = $this->request->getPost('workreportTo');
        }
        else {
            $user_id = $this->currentUser->id;
            $workreportStartDate = date('Y-m-d', strtotime('-7 day', time()));
            $workreportEndDate = date('Y-m-d');
        }

        if ($user_id != 0) {
            $attendances = Attendance::find('user_id = "' . $user_id . '" AND date >= "' . $workreportStartDate . '" AND date <= "' . $workreportEndDate . '"');
        }
        else {
            $attendances = Attendance::find('date >= "' . $workreportStartDate . '" AND date <= "' . $workreportEndDate . '"');
        }

        foreach ($attendances AS $attendance) {
            if (!is_null($attendance->task_id) && $attendance->task_id != 0) {
                if (!isset($dates[$attendance->date])) {
                    $dates[$attendance->date] = array();
                }

                if (!isset($dates[$attendance->date][$attendance->user_id])){
                    $dates[$attendance->date][$attendance->user_id] = array();
                }

                $dates[$attendance->date][$attendance->user_id][$attendance->task_id] = $attendance->task_id;
            }
        }

        foreach($dates AS $date => $_user_ids) {
            foreach ($_user_ids AS $_user_id => $task_ids) {
                $_user = User::findFirst('id = "' . $_user_id . '"');

                if (!$_user) {
                    continue;
                }

                foreach ($task_ids AS $task_id) {
                    $timeSpentStamp = 0;
                    $task = Task::findFirst('id = "' . $task_id . '"');
                    $attendances = Attendance::find('user_id = "' . $_user_id . '" AND date = "' . $date . '" AND task_id = "' . $task_id . '"');

                    foreach ($attendances AS $attendance) {
                        $start = strtotime($attendance->start);

                        if (is_null($attendance->end) && $attendance->date == date('Y-m-d')) {
                            $end = time();
                        }
                        else if (is_null($attendance->end)) {
                            $end = strtotime($attendance->start);
                        }
                        else {
                            $end = strtotime($attendance->end);
                        }

                        $timeSpentStamp += $end - $start;
                    }

                    $oldTimeZone = date_default_timezone_get();
                    date_default_timezone_set('UTC');
                    $timeSpent = date('H:i', $timeSpentStamp);
                    date_default_timezone_set($oldTimeZone);

                    if (!$task) {
                        continue;
                    }

                    $temp = array();
                    $temp['Date'] = $date;
                    $temp['User'] = $_user->full_name;
                    $temp['JobId'] = $task->job_id;
                    $temp['Project'] = $task->getProject()->name;
                    $temp['Task'] = $task->title;
                    $temp['TimeSpent'] = $timeSpent;

                    $records[] = $temp;
                }
            }
        }

        $this->view->setVar('records', $records);
        $this->view->setVar('workreportStartDate', $workreportStartDate);
        $this->view->setVar('workreportEndDate', $workreportEndDate);
        $this->view->setVar('workreportUserId', $user_id);
        $this->view->setVar('allUsers', $allUsers);
        $this->view->setVar('body_id', 'report_workreport');
        Phalcon\Tag::setTitle('Reports | Work Report');
    }
}
