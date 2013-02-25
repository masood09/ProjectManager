<?php
// Copyright (C) 2013 Masood Ahmed

// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.

// You should have received a copy of the GNU General Public License
// along with this program. If not, see <http://www.gnu.org/licenses/>.

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
        $this->belongsTo('created_by', 'User', 'id');
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

    public function getAllNotes()
    {
        $notes = Note::find(array(
            'conditions' => 'project_id=' . $this->id,
            'order' => 'created_at DESC',
        ));

        return $notes;
    }

    public function getProjectFiles($inclTasks = true)
    {
        if ($inclTasks) {
            $uploads = Upload::find(array(
                'conditions' => 'project_id = "' . $this->id . '"',
                'order' => 'uploaded_at DESC',
            ));
        }
        else {
            $uploads = Upload::find(array(
                'conditions' => 'project_id = "' . $this->id . '" AND task_id IS NULL AND comment_id IS NULL',
                'order' => 'uploaded_at DESC',
            ));
        }

        return $uploads;
    }

    public function getTaskFiles()
    {
        $uploads = Upload::find(array(
            'conditions' => 'project_id = "' . $this->id . '" AND (task_id IS NOT NULL OR comment_id IS NOT NULL)',
            'order' => 'uploaded_at DESC',
        ));

        return $uploads;
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

    public function hasTasksAssignedIn($user_id)
    {
        $UsersTasks = Task::findFirst('project_id = "' . $this->id . '" AND assigned_to = "' . $user_id . '"');

        if ($UsersTasks) {
            return true;
        }
        else {
            return false;
        }
    }
}
