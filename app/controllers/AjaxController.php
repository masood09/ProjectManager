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
    protected function _generateNotificationsHtml()
    {
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
        $this->view->render('partials', 'header_notification');
        $this->view->finish();

        return $this->view->getContent();
    }

    public function dashboardAction()
    {
        $return['openTasksCount'] = $this->currentUser->getOpenTasksCount();
        $return['allTasksCount'] = $this->currentUser->getAllTasksCount();
        $return['taskPercent'] = ceil ((($return['allTasksCount'] - $return['openTasksCount']) / $return['allTasksCount']) * 100);
        $return['userTodaysTime'] = $this->currentUser->getTodaysTime();
        $return['userTodaysTimePercent'] = $this->currentUser->getTodaysTimePercent($return['userTodaysTime']);
        $return['userMonthsTime'] = $this->currentUser->getMonthsTime();
        $return['userMonthsTimePercent'] = $this->currentUser->getMonthsTimePercent($return['userMonthsTime']);
        $return['userTodaysProductivity'] = $this->currentUser->getTodaysProductivity($return['userTodaysTime']);
        $return['notificationsHtml'] = $this->_generateNotificationsHtml();

        echo json_encode($return);

        $this->view->disable();
    }

    public function projecttasksAction()
    {
        $return['notificationsHtml'] = $this->_generateNotificationsHtml();

        echo json_encode($return);

        $this->view->disable();
    }

    public function projectnotesAction()
    {
        $return['notificationsHtml'] = $this->_generateNotificationsHtml();

        echo json_encode($return);

        $this->view->disable();
    }

    public function projectfilesAction()
    {
        $return['notificationsHtml'] = $this->_generateNotificationsHtml();

        echo json_encode($return);

        $this->view->disable();
    }
}
