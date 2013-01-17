<?php

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
}
