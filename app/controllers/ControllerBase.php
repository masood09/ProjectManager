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

class ControllerBase extends Phalcon\Mvc\Controller
{
    protected $currentUser = null;
    protected $userTodaysTime = null;
    protected $currentAttendance = null;

    protected function _checkSystem()
    {
        if (!Config::keyExists('core/version')) {
            // If core/version does not exist, we set the value to 0.1.0
            Config::setValue('core/version', '0.1.0');
        }

        UpdateHelper::updateVersion(Config::getValue('core/version'), $this->AppVersion, $this->modelsMetadata);

        $users = User::getAllActiveUsers();

        foreach ($users AS $user) {
            $timestamp = strtotime($user->leaves_assigned_on);

            if ((int)date('Ym') > (int)date('Ym', $timestamp)) {
                $user->leaves = $user->getAllocatedLeavesCount();
                $user->leaves_assigned_on = new Phalcon\Db\RawValue('now()');
                $user->save();
            }
        }
    }

    protected function initialize()
    {
        $this->_checkSystem();

        $this->currentUser = User::findFirst('id = "' . $this->session->get('user_id') . '"');
        $this->view->setVar('AppName', Config::getValue('core/name'));
        $this->view->setVar('controller', $this->dispatcher->getControllerName());
        $this->view->setVar('action', $this->dispatcher->getActionName());
        $this->view->setVar('url_params', '');
        $this->view->setVar("body_id", null);
        $this->view->setVar("body_class", null);

        if ($this->currentUser) {
            $this->view->setVar("allProjects", $this->currentUser->getAllProjects());

            $notifications = Notification::find(array(
                'conditions' => 'user_id = "' . $this->currentUser->id . '" AND read = 0',
                'order' => 'created_at DESC',
            ));

            $this->view->setVar('notifications', $notifications);

            $this->userTodaysTime = $this->currentUser->getDaysTime();
            $openTasksCount = $this->currentUser->getOpenTasksCount();
            $allTasksCount = $this->currentUser->getAllTasksCount();
            $closedTasksCount = $allTasksCount - $openTasksCount;

            $this->view->setVar('openTasksCount', $openTasksCount);
            $this->view->setVar('allTasksCount', $allTasksCount);
            $this->view->setVar('taskPercent', ceil (($closedTasksCount / $allTasksCount) * 100));

            $userTodaysTime = $this->userTodaysTime;
            $userTodaysTimePercent = $this->currentUser->getDaysTimePercent($userTodaysTime);
            $userMonthsTime = $this->currentUser->getMonthsTime();
            $userMonthsTimePercent = $this->currentUser->getMonthsTimePercent($userMonthsTime);
            $userTodaysProductivity = $this->currentUser->getDaysProductivity($userTodaysTime);

            $this->view->setVar('userTodaysTime', $this->userTodaysTime);
            $this->view->setVar('userTodaysProductivity', $userTodaysProductivity);
            $this->view->setVar('userTodaysTimePercent', $userTodaysTimePercent);
            $this->view->setVar('userMonthsTime', $userMonthsTime);
            $this->view->setVar('userMonthsTimePercent', $userMonthsTimePercent);

            $this->attendance = Attendance::findFirst('user_id = "' . $this->currentUser->id . '" AND date = CURDATE() AND end IS NULL');

            if ($this->attendance) {
                $this->view->setVar('currentAttendance', $this->attendance);
                $this->view->setVar('userTasks', null);
            }
            else {
                $this->view->setVar('currentAttendance', null);
                $this->view->setVar('userTasks', $this->currentUser->getAllTasks());
            }
        }
    }
}
