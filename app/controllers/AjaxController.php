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

class AjaxController extends ControllerBase
{
    public function getupdatesAction($lastUpdate = null)
    {
        if (is_null($lastUpdate)) {
            $lastUpdate = time();
        }

        $return['openTasksCount'] = $this->currentUser->getOpenTasksCount();
        $return['allTasksCount'] = $this->currentUser->getAllTasksCount();
        $return['taskPercent'] = ceil ((($return['allTasksCount'] - $return['openTasksCount']) / $return['allTasksCount']) * 100);
        $return['userTodaysTime'] = $this->currentUser->getTodaysTime();
        $return['userTodaysTimePercent'] = $this->currentUser->getTodaysTimePercent($return['userTodaysTime']);
        $return['userMonthsTime'] = $this->currentUser->getMonthsTime();
        $return['userMonthsTimePercent'] = $this->currentUser->getMonthsTimePercent($return['userMonthsTime']);
        $return['userTodaysProductivity'] = $this->currentUser->getTodaysProductivity($return['userTodaysTime']);

        $notifications = Notification::find(array(
            'conditions' => 'user_id = "' . $this->currentUser->id . '" AND created_at >= "' . date('Y-m-d H:i:s', $lastUpdate) . '" AND read = "0"',
            'order' => 'created_at DESC',
        ));

        $return['notificationsCount'] = count($notifications);

        if (count($notifications) > 0) {
            $return['notificationDropdown'] = '';
            foreach ($notifications AS $notification) {
                $return['notificationDropdown'] .= '<a href="' . $this->url->get($notification->getUrl()) . '" class="activity-preview new"><div class="media"><img class="media-object pull-left" src="' . $this->url->get('profile/' . $notification->getUser()->getProfilePicture()) . '"><abbr class="date pull-right">' . date('D, j M Y H:i:s O', strtotime($notification->created_at)) . '</abbr><div class="media-body"><p class="media-heading span4">' . $notification->message . '</p></div></div></a>
                ';
            }
        }
        else {
            $return['notificationDropdown'] = '';
        }

        if ($this->attendance) {
            $return['currentTaskTime'] = $this->attendance->getTimeSpent();
        }

        $return ['lastUpdate'] = time();

        echo json_encode($return);

        $this->view->disable();
        return;
    }
}
