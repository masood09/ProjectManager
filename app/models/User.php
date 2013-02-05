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
        $tasks = Task::find(array(
            'conditions' => 'assigned_to = "' . $this->id . '" AND status = 0',
            'order' => 'created_at DESC'
        ));

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
            }

            if (is_null($attendance->end)) {
                $end = time();
            }
            else {
                $end = strtotime($attendance->end);
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

            if (is_null($attendance->end)) {
                $end = time();
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
        $targetTime = 8;
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

        $daysTargetTime = 8;
        $startDate = date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
        $endDate = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));
        $workingDays = AttendanceHelper::getWorkingDays($startDate, $endDate);

        $explode = explode(':', $monthsTime);

        $_monthsTime = ($explode[0] * 60) + $explode[1];

        $_targetTime = ($daysTargetTime * $workingDays * 60);

        return ceil(($_monthsTime / $_targetTime) * 100);
    }

    public function getAllProjects()
    {
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

        $userTasks = TaskUser::find('user_id = "' . $this->id . '"');

        foreach ($userTasks AS $userTask) {
            $taskIds[] = $userTask->task_id;
        }

        if (count($taskIds) > 0) {
            $tasks = Task::find(array(
                'conditions' => 'id IN ("' . implode('", "', $taskIds) . '") AND status = 0',
                'order' => 'project_id ASC, created_at DESC'
            ));
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
}
