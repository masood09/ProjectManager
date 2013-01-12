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
}
