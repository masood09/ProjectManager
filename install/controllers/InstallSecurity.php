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

class InstallSecurity extends Phalcon\Mvc\User\Plugin
{
    public function __construct($dependencyInjector)
    {
        $this->_dependencyInjector = $dependencyInjector;
    }

    /**
     * This action is executed before execute any action in the application
     */
    public function beforeDispatch(Phalcon\Events\Event $event, Phalcon\Mvc\Dispatcher $dispatcher)
    {
        $controller = $dispatcher->getControllerName();
        $action = $dispatcher->getActionName();

        if ($controller != 'install') {
            $dispatcher->forward(
                array(
                    'controller' => 'install',
                    'action' => 'index'
                )
            );
        }

        if (!in_array($action, array('index', 'start', 'configuration', 'finish'))) {
            $dispatcher->forward(
                array(
                    'controller' => 'install',
                    'action' => 'index'
                )
            );
        }

        $this->view->setVar('currentUser', null);
    }
}
