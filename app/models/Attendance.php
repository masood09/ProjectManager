<?php

class Attendance extends Phalcon\Mvc\Model
{
	public function validation()
    {
        if ($this->validationHasFailed() == true) {
            return false;
        }
    }

    public function initialize()
    {
        $this->belongsTo('user_id', 'User', 'id');
        $this->belongsTo('task_id', 'Task', 'id');
    }

    public function getTaskTitleText()
    {
        if ($this->task_id == 0) {
            return "No Task";
        }

        $task = $this->getTask();
        $project = $task->getProject();

        return $project->name . ' - ' . $task->title;
    }
}
