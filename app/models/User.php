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

use Phalcon\Mvc\Model\Validator\Uniqueness as UniquenessValidator;

class User extends Phalcon\Mvc\Model
{
    public function validation()
    {
        $this->validate(new UniquenessValidator(array(
            'field' => 'email',
            'message' => 'The email is already registered. Try to log in using the email address.',
        )));

        if ($this->validationHasFailed() == true) {
            return false;
        }
    }

    public function isAdmin()
    {
        if ($this->role_id == 1) {
            return true;
        }

        return false;
    }

    public function getTasksAssigned()
    {
        $tasks = array();
        $projects = array();

        $tasks = Task::find(array(
            'conditions' => 'assigned_to = "' . $this->id . '" AND status = 0',
            'order' => 'created_at DESC'
        ));

        foreach ($tasks AS $task) {
            $projects[$task->getProject()->name][] = $task;
        }

        ksort($projects);
        $tasks = array();

        foreach ($projects AS $project) {
            foreach ($project AS $task) {
                $tasks[] = $task;
            }
        }

        return $tasks;
    }

    public function getOpenTasksCount()
    {
        $openTasks = Task::find('assigned_to = "' . $this->id . '" AND status = "0"');

        return count($openTasks);
    }

    public function getAllTasksCount()
    {
        $allTasks = Task::find('assigned_to = "' . $this->id . '"');

        return count($allTasks);
    }

    public function getDaysProductivity($daysTime, $date=null, $month=null, $year = null)
    {
        if (is_null($date)) {
            $date = date('d');
        }

        if (is_null($month)) {
            $month = date('m');
        }

        if (is_null($year)) {
            $year = date('Y');
        }

        $start = 0;
        $end = 0;

        $attendances = Attendance::find('user_id = "' . $this->id . '" AND DAY(date) = "' . $date. '" AND MONTH(date) = "' . $month . '" AND YEAR(date) = "' . $year . '"');

        $i = 0;

        foreach ($attendances AS $attendance) {
            if ($i == 0) {
                $start = strtotime($attendance->start);
                $lastEnd = $start;
            }

            if (is_null($attendance->end) && $attendance->date == date('Y-m-d')) {
                $end = time();
            }
            else if (is_null($attendance->end)) {
                $end = $lastEnd;
            }
            else {
                $end = strtotime($attendance->end);
                $lastEnd = $end;
            }

            $i++;
        }

        $totalDaysTimeStamp = $end - $start;

        if ($totalDaysTimeStamp == 0) {
            return 0;
        }

        $oldTimeZone = date_default_timezone_get();
        date_default_timezone_set('UTC');
        $totalDaysTime = date('H:i', $totalDaysTimeStamp);
        date_default_timezone_set($oldTimeZone);

        $explode = explode(':', $daysTime);
        $_daysTime = ($explode[0] * 60) + $explode[1];
        $explode = explode(':', $totalDaysTime);
        $_totalDaysTime = ($explode[0] * 60) + $explode[1];

        return ceil(($_daysTime / $_totalDaysTime) * 100);
    }

    public function getDaysTime($date=null, $month=null, $year=null)
    {
        if (is_null($date)) {
            $date = date('d');
        }

        if (is_null($month)) {
            $month = date('m');
        }

        if (is_null($year)) {
            $year = date('Y');
        }

        $daysTimeStamp = 0;

        $attendances = Attendance::find('user_id = "' . $this->id . '" AND DAY(date) = "' . $date. '" AND MONTH(date) = "' . $month . '" AND YEAR(date) = "' . $year . '"');

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

            $daysTimeStamp += $end - $start;
        }

        $oldTimeZone = date_default_timezone_get();
        date_default_timezone_set('UTC');
        $daysTime = date('H:i', $daysTimeStamp);
        date_default_timezone_set($oldTimeZone);

        return $daysTime;
    }

    public function getDaysTimePercent($todaysTime)
    {
        $targetTime = (int)Config::getValue('attendance/days_target_time');
        $explode = explode(':', $todaysTime);

        $_daysTime = ($explode[0] * 60) + $explode[1];

        $_targetTime = ($targetTime * 60);

        return ceil(($_daysTime / $_targetTime) * 100);
    }

    public function getMonthsTime($month=null, $year=null)
    {
        if (is_null($month)) {
            $month = date('m');
        }

        if (is_null($year)) {
            $year = date('Y');
        }

        $monthsTimeStamp = 0;

        $attendances = Attendance::find('user_id = "' . $this->id . '" AND MONTH(date) = "' . $month . '" AND YEAR(date) = "' . $year . '"');

        foreach ($attendances AS $attendance) {
            $start = strtotime($attendance->start);

            if (is_null($attendance->end) && $attendance->date == date('Y-m-d')) {
                $end = time();
            }
            else if (is_null($attendance->end)) {
                $end = $start;
            }
            else {
                $end = strtotime($attendance->end);
            }

            $monthsTimeStamp += ($end - $start);
        }

        $oldTimeZone = date_default_timezone_get();
        date_default_timezone_set('UTC');
        $monthsTime = date('j:H:i', $monthsTimeStamp);
        date_default_timezone_set($oldTimeZone);

        $explode = explode(':', $monthsTime);
        $monthsTime = ((($explode[0] - 1) * 24) + ($explode[1])) . ':' . $explode[2];

        return $monthsTime;
    }

    public function getMonthsTimePercent($monthsTime, $month=null, $year=null)
    {
        if (is_null($month)) {
            $month = date('m');
        }

        if (is_null($year)) {
            $year = date('Y');
        }

        $daysTargetTime = (int)Config::getValue('attendance/days_target_time');
        $startDate = date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
        $endDate = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));

        $workingDays = AttendanceHelper::getWorkingDays($this->id, $startDate, $endDate);

        $explode = explode(':', $monthsTime);

        $_monthsTime = ($explode[0] * 60) + $explode[1];

        $_targetTime = ($daysTargetTime * $workingDays * 60);

        return ceil(($_monthsTime / $_targetTime) * 100);
    }

    public function getAllProjects()
    {
        if ($this->isAdmin()) {
            $projects = Project::find(array(
                'order' => 'name ASC'
            ));

            return $projects;
        }

        $projects = array();
        $projectUsers = array();
        $projectIds = array();

        $projectUsers = ProjectUser::find('user_id="' . $this->id . '"');

        foreach($projectUsers AS $projectUser) {
            $projectIds[] = $projectUser->project_id;
        }

        if (count($projectIds) > 0) {
            $projects = Project::find(array(
                'conditions' => 'id IN ("' . implode('", "', $projectIds) . '")',
                'order' => 'name ASC'
            ));
        }

        return $projects;
    }

    public function getAllTasks()
    {
        $tasks = array();
        $userTasks = array();
        $taskIds = array();
        $projects = array();

        $userTasks = TaskUser::find('user_id = "' . $this->id . '"');

        foreach ($userTasks AS $userTask) {
            $taskIds[] = $userTask->task_id;
        }

        if (count($taskIds) > 0) {
            $tasks = Task::find(array(
                'conditions' => 'id IN ("' . implode('", "', $taskIds) . '") AND status = 0',
                'order' => 'project_id DESC, created_at DESC'
            ));

            foreach ($tasks AS $task) {
                $projects[$task->getProject()->name][] = $task;
            }

            ksort($projects);
            $tasks = array();

            foreach ($projects AS $project) {
                foreach ($project AS $task) {
                    $tasks[] = $task;
                }
            }
        }

        return $tasks;
    }

    public function getProfilePicture()
    {
        if (file_exists(__DIR__ . '/../../public/profile/' . $this->id . '.jpg')) {
            return $this->id . '.jpg';
        }

        return 'default.jpg';
    }

    public function getAllocatedLeavesCount()
    {
        if (Config::getValue('attendance/leaves_carries') == '1') {
            $canCarryOver = true;
        }
        else {
            $canCarryOver = false;
        }

        $query = new Phalcon\Mvc\Model\Query('SELECT MIN(date) AS start_date, MAX(date) AS end_date FROM Attendance WHERE user_id = "' . $this->id . '"');
        $query->setDI($this->getDi());
        $attendances = $query->execute();

        $months = 0;
        $years = 0;
        $leaves = 0;

        $start = time();
        $end = time();

        foreach ($attendances AS $attendance) {
            if (!is_null($attendance->start_date)) {
                $start = strtotime($attendance->start_date);
            }

            if (!is_null($attendance->end_date)) {
                $end = strtotime($attendance->end_date);
            }
        }

        $start_year = (int)date('Y', $start);
        $end_year = (int)date('Y', $end);
        $start_month = (int)date('m', $start);
        $end_month = (int)date('m', $end);

        $months = ((($end_year * 12) + $end_month) - (($start_year * 12) + $start_month)) + 1;
        $years = ($end_year - $start_year) + 1;

        if (Config::getValue('attendance/leaves_method') === 'month') {
            if ($canCarryOver) {
                $leaves = ($months * (int)Config::getValue('attendance/leaves_per_month'));
            }
            else {
                $leaves = (int)Config::getValue('attendance/leaves_per_month');
            }
        }
        else if (Config::getValue('attendance/leaves_method') === 'quarter') {
            $startCounterDate = strtotime(date('Y-m-1', $start));
            $endCounterDate = strtotime(date('Y-m-1', $end));

            if (!in_array((int)date('m', $startCounterDate), array(1, 4, 7, 10))) {
                $_leavesPerMonth = ((int)Config::getValue('attendance/leaves_per_quarter')) / 3;

                if (date('m', $startCounterDate) < 4) {
                    $leaves = (4 - date('m', $startCounterDate)) * $_leavesPerMonth;
                }
                else if (date('m', $startCounterDate) < 7) {
                    $leaves = (7 - date('m', $startCounterDate)) * $_leavesPerMonth;
                }
                else if (date('m', $startCounterDate) < 10) {
                    $leaves = (10 - date('m', $startCounterDate)) * $_leavesPerMonth;
                }
                else {
                    $leaves = (13 - date('m', $startCounterDate)) * $_leavesPerMonth;
                }
            }

            while (strtotime('+1 MONTH', $startCounterDate) <= $endCounterDate) {
                if (in_array((int)date('m', $startCounterDate), array(1, 4, 7, 10))) {
                    if ($canCarryOver) {
                        $leaves += (int)Config::getValue('attendance/leaves_per_quarter');
                    }
                    else {
                        $leaves = (int)Config::getValue('attendance/leaves_per_quarter');
                    }
                }

                $startCounterDate = strtotime('+1 MONTH', $startCounterDate);
            }

            if (in_array((int)date('m', $endCounterDate), array(1, 4, 7, 10))) {
                if ($canCarryOver) {
                    $leaves += (int)Config::getValue('attendance/leaves_per_quarter');
                }
                else {
                    $leaves = (int)Config::getValue('attendance/leaves_per_quarter');
                }
            }
        }
        else if (Config::getValue('attendance/leaves_method') === 'year') {
            if ($canCarryOver) {
                $startCounterDate = strtotime(date('Y-m-01', $start));
                $endCounterDate = strtotime(date('Y-01-01', $end));

                $_leavesPerMonth = ((int)Config::getValue('attendance/leaves_per_year')) / 12;

                while (strtotime('+1 MONTH', $startCounterDate) <= $endCounterDate) {
                    $leaves += $_leavesPerMonth;
                    $startCounterDate = strtotime('+1 MONTH', $startCounterDate);
                }

                $thisYearsMonths = 12 - (int)date('m', $start) + 1;

                if ($thisYearsMonths == 12) {
                    $leaves += (int)Config::getValue('attendance/leaves_per_year');
                }
                else {
                    $leaves += $_leavesPerMonth * $thisYearsMonths;
                }
            }
            else {
                $thisYearsMonths = 12 - (int)date('m', $start) + 1;

                if ($thisYearsMonths == 12) {
                    $leaves = (int)Config::getValue('attendance/leaves_per_year');
                }
                else {
                    $leaves = $_leavesPerMonth * $thisYearsMonths;
                }
            }
        }

        return $leaves;
    }

    public function getAvailLeavesCount()
    {
        $allocatedLeaves = $this->leaves;

        $leaves = Leaves::find('user_id = "' . $this->id . '" AND approved = "1"');

        return $allocatedLeaves - count($leaves);
    }

    public function getPendingLeavesCount()
    {
        $leaves = Leaves::find('user_id = "' . $this->id . '" AND approved IS NULL');

        return count($leaves);
    }

    public function getWeekOffs()
    {
        if (is_null($this->weekoffs)) {
            $weekoffs = Config::getValue('attendance/weekoffs');
        }
        else {
            $weekoffs = $this->weekoffs;
        }

        return explode(',', $weekoffs);
    }

    static function getAllUsers()
    {
        $allUsers = User::find(array(
            'order' => 'full_name ASC',
        ));

        return $allUsers;
    }

    static function getAllActiveUsers()
    {
        $allUsers = User::find(array(
            'conditions' => 'is_active = "1"',
            'order' => 'full_name ASC',
        ));

        return $allUsers;
    }

    static function getAllNonActiveUsers()
    {
        $allUsers = User::find(array(
            'conditions' => 'is_active = "0"',
            'order' => 'full_name ASC',
        ));

        return $allUsers;
    }
}
