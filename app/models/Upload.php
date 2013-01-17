<?php

class Upload extends Phalcon\Mvc\Model
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
        $this->belongsTo('project_id', 'Project', 'id');
        $this->belongsTo('task_id', 'Task', 'id');
        $this->belongsTo('comment_id', 'Comment', 'id');
    }
}
