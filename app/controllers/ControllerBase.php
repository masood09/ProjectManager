<?php

class ControllerBase extends Phalcon\Mvc\Controller
{
    protected $currentUser = null;

    protected function _checkSystem()
    {
        if (!Config::keyExists('core/version')) {
            // If core/version does not exist, we set the value to 0.1.0
            Config::setValue('core/version', '0.1.0');
        }

        UpdateHelper::updateVersion(Config::getValue('core/version'), $this->AppVersion, $this->modelsMetadata);
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
        }
    }
}
