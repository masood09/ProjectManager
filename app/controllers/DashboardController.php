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

class DashboardController extends ControllerBase
{
    public function indexAction()
    {
        $this->view->setVar("tasksAssigned", $this->currentUser->getTasksAssigned());

        $this->view->setVar('body_id', 'dashboard');


        $activities = Notification::find(array(
            'conditions' => 'user_id = "' . $this->currentUser->id . '"',
            'order' => 'created_at DESC',
        ));

        $this->view->setVar('activities', $activities);

        Phalcon\Tag::setTitle('Dashboard');
    }
}
