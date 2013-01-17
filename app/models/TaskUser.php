<?php

class TaskUser extends Phalcon\Mvc\Model
{
	public function initialize()
    {
        $this->belongsTo('task_id', 'Task', 'id');
        $this->belongsTo('user_id', 'User', 'id');
    }
}
