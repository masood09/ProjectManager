<?php

class AjaxController extends ControllerBase
{
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

        echo json_encode($return);

        $this->view->disable();
    }
}
