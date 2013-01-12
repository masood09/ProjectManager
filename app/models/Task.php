<?php

class Task extends Phalcon\Mvc\Model
{
	public function validation()
    {
        if ($this->validationHasFailed() == true) {
            return false;
        }
    }

    public function initialize()
    {
        $this->hasMany('id', 'TaskUser', 'task_id');
        $this->belongsTo('assigned_to', 'User', 'id');
        $this->hasMany('id', 'Comment', 'task_id');
        $this->belongsTo('project_id', 'Project', 'id');
    }

    public function getTimePercent()
    {
        if ($this->hours == 0) {
            return 0;
        }

        $tasksTimeStamp = 0;

        $attendances = Attendance::find('task_id = "' . $this->id . '"');

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

            $tasksTimeStamp += $end - $start;
        }

        $oldTimeZone = date_default_timezone_get();
        date_default_timezone_set('UTC');
        $tasksTime = date('H:i', $tasksTimeStamp);
        date_default_timezone_set($oldTimeZone);

        $explode = explode(':', $tasksTime);
        $_tasksTime = ($explode[0] * 60) + $explode[1];
        $explode = explode(':', $this->hours);
        $_targetTime = ($explode[0] * 60) + $explode[1];

        return ceil(($_tasksTime / $_targetTime) * 100);
    }
}
