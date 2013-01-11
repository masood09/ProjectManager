<?php

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

        if (!in_array($action, array('index', 'step1', 'step2'))) {
            $dispatcher->forward(
                array(
                    'controller' => 'install',
                    'action' => 'index'
                )
            );
        }
    }
}
