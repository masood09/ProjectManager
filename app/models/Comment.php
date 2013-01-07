<?php

class Comment extends Phalcon\Mvc\Model
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

    public function getUploads()
    {
        $uploads = Upload::find('task_id = "' . $this->task_id . '" AND comment_id = "' . $this->id . '"');

        if (count($uploads) > 0) {
            return $uploads;
        }

        return array();
    }
}
