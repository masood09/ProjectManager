<?php

use Phalcon\Mvc\Model\Validator\Uniqueness as UniquenessValidator;

class Project extends Phalcon\Mvc\Model
{
	public function validation()
    {
        $this->validate(new UniquenessValidator(array(
            'field' => 'name',
            'message' => 'The Project already registered.',
        )));

        if ($this->validationHasFailed() == true) {
            return false;
        }
    }

    public function initialize()
    {
        $this->hasMany('id', 'ProjectUser', 'project_id');
        $this->hasMany('id', 'Task', 'project_id');
        $this->hasMany('id', 'Note', 'project_id');
    }

    public function isInProject($user)
    {
        $adminUsers = User::find('role_id="' . 1 . '"');

        foreach($adminUsers AS $adminUser) {
            // Let's check whether the user is already present in the project.
            $projectUser = null;
            $projectUser = ProjectUser::findFirst('project_id="' . $this->id . '" AND user_id="' . $adminUser->id . '"');

            if (!$projectUser) {
                $projectUser = new ProjectUser();
                $projectUser->user_id = $adminUser->id;
                $projectUser->project_id = $this->id;
                $projectUser->created_at = new Phalcon\Db\RawValue('now()');

                $projectUser->save();
            }
        }

        $projectUser = ProjectUser::findFirst('user_id="' . $user->id . '" AND project_id="' . $this->id . '"');

        if ($projectUser) {
            return true;
        }

        return false;
    }

    public function getAllTasks()
    {
        $tasks = Task::find(array(
            'conditions' => 'project_id=' . $this->id,
            'order' => 'status ASC, created_at DESC',
        ));

        return $tasks;
    }

    public function getProjectUsers()
    {
        $users = array();
        $userIds = array();

        $ProjectUsers = ProjectUser::find('project_id = "' . $this->id . '"');

        foreach($ProjectUsers AS $ProjectUser) {
            $userIds[] = $ProjectUser->user_id;
        }

        if (count($userIds) > 0) {
            $users = User::find(array(
                'conditions' => 'id IN ("' . implode('", "', $userIds) . '")',
                'order' => 'full_name ASC'
            ));
        }

        return $users;
    }
}
