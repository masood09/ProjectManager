<?php

class Notification extends Phalcon\Mvc\Model
{
    public function validation()
    {
        if ($this->validationHasFailed() == true) {
            return false;
        }
    }

    public function initialize()
    {
        $this->belongsTo('created_by', 'User', 'id');
        $this->belongsTo('project_id', 'Project', 'id');
        $this->belongsTo('task_id', 'Task', 'id');
        $this->belongsTo('comment_id', 'Comment', 'id');
        $this->belongsTo('note_id', 'Note', 'id');
        $this->belongsTo('upload_id', 'Upload', 'id');
    }
}
