<?php

class DashboardController extends ControllerBase
{
    public function indexAction()
    {
        $openTasksCount = $this->currentUser->getOpenTasksCount();
        $allTasksCount = $this->currentUser->getAllTasksCount();
        $closedTasksCount = $allTasksCount - $openTasksCount;

        $this->view->setVar('openTasksCount', $openTasksCount);
        $this->view->setVar('allTasksCount', $allTasksCount);
        $this->view->setVar('taskPercent', ceil (($closedTasksCount / $allTasksCount) * 100));

        $userTodaysTime = $this->currentUser->getTodaysTime();
        $userTodaysTimePercent = $this->currentUser->getTodaysTimePercent($userTodaysTime);
        $userMonthsTime = $this->currentUser->getMonthsTime();
        $userMonthsTimePercent = $this->currentUser->getMonthsTimePercent($userMonthsTime);
        $userTodaysProductivity = $this->currentUser->getTodaysProductivity($userTodaysTime);

        $this->view->setVar('userTodaysProductivity', $userTodaysProductivity);
        $this->view->setVar('userTodaysTime', $userTodaysTime);
        $this->view->setVar('userTodaysTimePercent', $userTodaysTimePercent);
        $this->view->setVar('userMonthsTime', $userMonthsTime);
        $this->view->setVar('userMonthsTimePercent', $userMonthsTimePercent);

        $this->view->setVar('body_id', 'dashboard');


        $activities = Notification::find(array(
            'conditions' => 'user_id = "' . $this->currentUser->id . '"',
            'order' => 'created_at DESC',
        ));

        $this->view->setVar('activities', $activities);

        Phalcon\Tag::setTitle('Dashboard');
    }
}
