<?php

use Phalcon\Mvc\Model\Validator\Uniqueness as UniquenessValidator;

class User extends Phalcon\Mvc\Model
{
	public function validation()
    {
        $this->validate(new UniquenessValidator(array(
            'field' => 'email',
            'message' => 'The email is already registered. Try to log in using the email address.',
        )));

        if ($this->validationHasFailed() == true) {
            return false;
        }
    }

    public function initialize()
    {
        $this->belongsTo('role_id', 'Role', 'id');
        $this->hasMany('id', 'Session', 'user_id');
    }

    public function getAllProjects()
    {
        $projectUsers = ProjectUser::find('user_id="' . $this->id . '"');

        $projects = null;
        $projectIds = null;

        foreach($projectUsers AS $projectUser) {
            $projectIds[] = $projectUser->project_id;
        }

        if (count($projectIds) > 0) {
            $projectId = implode(" OR id=", $projectIds);

            $projects = Project::find(array(
                'conditions' => 'id=' . $projectId,
                'order' => 'created_at DESC, name ASC'
            ));
        }

        return $projects;
    }

    static function getAllDevelopers($inclAdmin=false)
    {
        if ($inclAdmin) {
            $users = User::find(array(
                'conditions' => 'role_id="' . 2 . '" OR role_id="' . 1 . '"',
                'order' => 'full_name'
            ));
        }
        else {
            $users = User::find(array(
                'conditions' => 'role_id="' . 2 . '"',
                'order' => 'full_name'
            ));
        }

        if (count($users) > 0) {
            return $users;
        }

        return null;
    }

    public function getAllTasks()
    {
        $tasks = array();
        $taskUsers = TaskUser::find('user_id="' . $this->id . '"');

        foreach($taskUsers AS $taskUser) {
            $tasks[] = $taskUser->getTask();
        }

        return $tasks;
    }

    public function isAdmin()
    {
        if ($this->role_id == 1) {
            return true;
        }

        return false;
    }
}
