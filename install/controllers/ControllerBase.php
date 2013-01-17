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
    protected function initialize()
    {
        $this->view->setVar('AppName', 'Project Manager');
        $this->view->setVar('controller', $this->dispatcher->getControllerName());
        $this->view->setVar('action', $this->dispatcher->getActionName());
        $this->view->setVar('url_params', '');
        $this->view->setVar("body_id", null);
        $this->view->setVar("body_class", null);
    }
}
