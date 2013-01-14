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

    public function getUrl()
    {
        if ($this->project_id != null &&
            $this->task_id == null &&
            $this->comment_id == null &&
            $this->note_id == null &&
            $this->upload_id == null
        )
        {
            // This is a project notification.
            return 'project/view/' . $this->project_id;
        }

        if ($this->task_id != null &&
            $this->comment_id == null
        )
        {
            // This is a task notification.
            return 'project/view/' . $this->project_id . '/' . $this->task_id;
        }

        if ($this->comment_id != null) {
            // This is a comment notification.
            return 'project/view/' . $this->project_id . '/' . $this->task_id . '#comment-' . $this->comment_id;
        }

        if ($this->note_id != null) {
            // This is a note notification.
            return 'project/notes/' . $this->project_id . '/' . $this->note_id;
        }

        if ($this->upload_id != null) {
            // This is a upload notification.
            return 'project/files/' . $this->project_id . '#upload-' . $this->upload_id;
        }

        return '';
    }
}
