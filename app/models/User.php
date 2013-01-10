<?php

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

    public function getTodaysProductivity($todaysTime)
    {
        $start = 0;
        $end = 0;

        $attendances = Attendance::find('user_id = "' . $this->id . '" AND date = CURDATE()');

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

        $totalTodaysTimeStamp = $end - $start;

        if ($totalTodaysTimeStamp == 0) {
            return 0;
        }

        $oldTimeZone = date_default_timezone_get();
        date_default_timezone_set('UTC');
        $totalTodaysTime = date('H:i', $totalTodaysTimeStamp);
        date_default_timezone_set($oldTimeZone);

        $explode = explode(':', $todaysTime);
        $_todaysTime = ($explode[0] * 60) + $explode[1];
        $explode = explode(':', $totalTodaysTime);
        $_totalTodaysTime = ($explode[0] * 60) + $explode[1];

        return ceil(($_todaysTime / $_totalTodaysTime) * 100);
    }

    public function getTodaysTime()
    {
        $todaysTimeStamp = 0;

        $attendances = Attendance::find('user_id = "' . $this->id . '" AND date = CURDATE()');

        foreach ($attendances AS $attendance) {
            $start = strtotime($attendance->start);

            if (is_null($attendance->end)) {
                $end = time();
            }
            else {
                $end = strtotime($attendance->end);
            }

            $todaysTimeStamp += $end - $start;
        }

        $oldTimeZone = date_default_timezone_get();
        date_default_timezone_set('UTC');
        $todaysTime = date('H:i', $todaysTimeStamp);
        date_default_timezone_set($oldTimeZone);

        return $todaysTime;
    }

    public function getTodaysTimePercent($todaysTime)
    {
        $targetTime = 8;
        $explode = explode(':', $todaysTime);

        $_todaysTime = ($explode[0] * 60) + $explode[1];

        $_targetTime = ($targetTime * 60);

        return ceil(($_todaysTime / $_targetTime) * 100);
    }

    public function getMonthsTime()
    {
        $monthsTimeStamp = 0;

        $attendances = Attendance::find('user_id = "' . $this->id . '" AND MONTH(date) = MONTH(NOW()) AND YEAR(date) = YEAR(NOW())');

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

            $monthsTimeStamp += $end - $start;
        }

        $oldTimeZone = date_default_timezone_get();
        date_default_timezone_set('UTC');
        $monthsTime = date('H:i', $monthsTimeStamp);
        date_default_timezone_set($oldTimeZone);

        return $monthsTime;
    }

    public function getMonthsTimePercent($monthsTime)
    {
        $daysTargetTime = 8;
        $startDate = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
        $endDate = date('Y-m-t', mktime(0, 0, 0, date('m'), 1, date('Y')));
        $workingDays = AttendanceHelper::getWorkingDays($startDate, $endDate);

        $explode = explode(':', $monthsTime);

        $_monthsTime = ($explode[0] * 60) + $explode[1];

        $_targetTime = ($daysTargetTime * $workingDays * 60);

        return ceil(($_monthsTime / $_targetTime) * 100);
    }
}
