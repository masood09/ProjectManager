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

class Attendance extends Phalcon\Mvc\Model
{
    public function validation()
    {
        if ($this->validationHasFailed() == true) {
            return false;
        }
    }

    public function initialize()
    {
        $this->belongsTo('user_id', 'User', 'id');
        $this->belongsTo('task_id', 'Task', 'id');
    }

    public function getTimeSpent()
    {
        $timeStamp = 0;

        $start = strtotime($this->start);

        if (is_null($this->end) && $this->date == date('Y-m-d')) {
            $end = time();
        }
        else if (is_null($this->end)) {
            $end = $start;
        }
        else {
            $end = strtotime($this->end);
        }

        $timeStamp += ($end - $start);

        $oldTimeZone = date_default_timezone_get();
        date_default_timezone_set('UTC');
        $time = date('j:H:i', $timeStamp);
        date_default_timezone_set($oldTimeZone);

        $explode = explode(':', $time);
        $time = ((($explode[0] - 1) * 24) + ($explode[1])) . ':' . $explode[2];

        return $time;
    }
}
