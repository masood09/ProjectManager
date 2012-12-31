<?php

class ProjectUser extends \Phalcon\Mvc\Model
{
    public function initialize()
    {
        $this->belongsTo('project_id', 'Project', 'id');
        $this->belongsTo('user_id', 'User', 'id');
    }
}
