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

class AttendanceHelper
{
    static function getHolidays($startDate = null, $endDate = null)
    {
        if ($startDate == null) {
            $startDate = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
        }

        if ($endDate == null) {
            $endDate = date('Y-m-t', mktime(0, 0, 0, date('m'), 1, date('Y')));
        }

        $results = null;
        $results = Holiday::find('date >= "' . $startDate . '" AND date <= "' . $endDate . '"');

        $holidays = array();

        foreach ($results AS $result) {
            $holidays[] = $result->date;
        }

        return $holidays;
    }

    static function getWorkingDays($user_id, $startDate = null, $endDate = null)
    {
        if ($startDate == null) {
            $startDate = date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
        }

        if ($endDate == null) {
            $endDate = date('Y-m-t', mktime(0, 0, 0, date('m'), 1, date('Y')));
        }

        $holidays = AttendanceHelper::getHolidays($startDate, $endDate);

        $leaves = Leaves::find('user_id = "' . $user_id . '" AND date >= "' . $startDate . '" AND date <= "' . $endDate . '" AND approved = "1"');

        foreach ($leaves AS $leave) {
            if (!in_array($leave->date, $holidays)) {
                array_push($holidays, $leave->date);
            }
        }

        $endDate = strtotime($endDate);
        $startDate = strtotime($startDate);
        $curDate = $startDate;

        $user = User::findFirst('id = "' . $user_id . '"');
        $weekoffs = $user->getWeekOffs();
        $workingDays = 0;

        while ($curDate <= $endDate) {
            if (!in_array(date('N', $curDate), $weekoffs)) {
                $workingDays++;
            }

            $curDate = strtotime('+1 day', $curDate);
        }

        foreach ($holidays AS $holiday) {
            $time_stamp = strtotime($holiday);

            if (!in_array(date('N', $time_stamp), $weekoffs)) {
                $workingDays--;
            }
        }

        return $workingDays;
    }
}
